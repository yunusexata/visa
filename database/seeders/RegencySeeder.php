<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegencySeeder extends Seeder
{
    public function run()
    {
        $file = storage_path('app/regencies.csv');

        if (!file_exists($file)) {
            $this->command->warn("File 'regencies.csv' not found at: {$file}. Skipping Regency data seeding.");
            return;
        }

        $rows = array_map('str_getcsv', file($file));

        $data = collect($rows)->map(function ($row) {

            $name = strtoupper($row[2]);

            // remove prefix
            // $name = str_replace(['KABUPATEN ', 'KOTA '], '', $name);

            return [
                'name' => trim($name),
            ];
        })
            ->unique('name')
            ->values()
            ->toArray();

        DB::table('regencies')->insert($data);
    }
}
