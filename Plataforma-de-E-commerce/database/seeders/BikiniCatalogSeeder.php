<?php

namespace Database\Seeders;

class BikiniCatalogSeeder extends PhotoCatalogSeeder
{
    protected function catalogPath(): string
    {
        return database_path('seeders/data/bikini-catalog.json');
    }
}
