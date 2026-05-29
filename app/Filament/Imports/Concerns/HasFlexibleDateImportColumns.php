<?php

namespace App\Filament\Imports\Concerns;

use App\Support\ImportDateParser;
use Filament\Actions\Imports\ImportColumn;

trait HasFlexibleDateImportColumns
{
    protected static function dateImportColumn(ImportColumn $column): ImportColumn
    {
        return $column
            ->helperText(ImportDateParser::helperText())
            ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => ImportDateParser::parse($originalState ?? $state));
    }
}
