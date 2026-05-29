<?php

namespace App\Filament\Clusters\HrMasterData;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class HrMasterDataCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Master HR';

    protected static ?string $clusterBreadcrumb = 'Master HR';
}
