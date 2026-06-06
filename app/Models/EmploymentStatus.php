<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmploymentStatus extends Model
{
    use HasFactory;

    public const DEFAULT_NAME = 'Tanpa Status Kerja';

    protected $fillable = [
        'name',
        'color',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $employmentStatus): void {
            if (
                $employmentStatus->exists &&
                $employmentStatus->getOriginal('name') === self::DEFAULT_NAME &&
                $employmentStatus->name !== self::DEFAULT_NAME
            ) {
                throw ValidationException::withMessages([
                    'name' => 'Nama status default Tanpa Status Kerja tidak boleh diubah.',
                ]);
            }

            if ($employmentStatus->name === self::DEFAULT_NAME) {
                $defaultExists = self::query()
                    ->where('name', self::DEFAULT_NAME)
                    ->when($employmentStatus->exists, fn ($query) => $query->whereKeyNot($employmentStatus->getKey()))
                    ->exists();

                if ($defaultExists) {
                    throw ValidationException::withMessages([
                        'name' => 'Status default Tanpa Status Kerja sudah ada.',
                    ]);
                }
            }
        });

        static::deleting(function (self $employmentStatus): void {
            if ($employmentStatus->isDefault()) {
                throw ValidationException::withMessages([
                    'employment_status' => 'Status default Tanpa Status Kerja tidak boleh dihapus.',
                ]);
            }

            DB::transaction(function () use ($employmentStatus): void {
                $defaultEmploymentStatus = self::getOrCreateDefault();

                Employee::query()
                    ->where('employment_status_id', $employmentStatus->getKey())
                    ->update(['employment_status_id' => $defaultEmploymentStatus->getKey()]);
            });
        });
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public static function getDefault(): ?self
    {
        return self::query()
            ->where('name', self::DEFAULT_NAME)
            ->first();
    }

    public static function getOrCreateDefault(): self
    {
        return self::query()->firstOrCreate(
            ['name' => self::DEFAULT_NAME],
            [
                'color' => 'gray',
                'description' => 'Status kerja fallback bawaan sistem.',
                'is_active' => true,
            ],
        );
    }

    public function isDefault(): bool
    {
        return $this->name === self::DEFAULT_NAME;
    }
}
