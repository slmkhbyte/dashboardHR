<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Position extends Model
{
    use HasFactory;

    public const DEFAULT_NAME = 'Tanpa Jabatan';

    protected $fillable = [
        'name',
        'code',
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
        static::saving(function (self $position): void {
            if ($position->exists && $position->getOriginal('name') === self::DEFAULT_NAME && $position->name !== self::DEFAULT_NAME) {
                throw ValidationException::withMessages([
                    'name' => 'Nama jabatan default Tanpa Jabatan tidak boleh diubah.',
                ]);
            }

            if ($position->name === self::DEFAULT_NAME) {
                $defaultExists = self::query()
                    ->where('name', self::DEFAULT_NAME)
                    ->when($position->exists, fn ($query) => $query->whereKeyNot($position->getKey()))
                    ->exists();

                if ($defaultExists) {
                    throw ValidationException::withMessages([
                        'name' => 'Jabatan default Tanpa Jabatan sudah ada.',
                    ]);
                }
            }
        });

        static::deleting(function (self $position): void {
            if ($position->isDefault()) {
                throw ValidationException::withMessages([
                    'position' => 'Jabatan default Tanpa Jabatan tidak boleh dihapus.',
                ]);
            }

            DB::transaction(function () use ($position): void {
                $defaultPosition = self::getOrCreateDefault();

                Employee::query()
                    ->where('position_id', $position->getKey())
                    ->update(['position_id' => $defaultPosition->getKey()]);
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
                'code' => null,
                'description' => 'Jabatan fallback bawaan sistem.',
                'is_active' => true,
            ],
        );
    }

    public function isDefault(): bool
    {
        return $this->name === self::DEFAULT_NAME;
    }
}
