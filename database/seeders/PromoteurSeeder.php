<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Promoteur;

class PromoteurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promoteurs = [];
        $chunks = 2000;
        $count = 100000;
        $now = \Carbon\Carbon::now()->format('Y-m-d H:i:s');

        for ($i = 0; $i < $count; $i++) {
            $promoteur = Promoteur::factory()->make();
            $data = $promoteur->toArray();
            
            // Convertir toutes les dates au format MySQL
            foreach (['datenaissance', 'created_at', 'updated_at'] as $dateField) {
                if (isset($data[$dateField])) {
                    if ($data[$dateField] instanceof \DateTime) {
                        $data[$dateField] = $data[$dateField]->format('Y-m-d H:i:s');
                    } elseif (is_string($data[$dateField]) && \Carbon\Carbon::hasFormat($data[$dateField], 'Y-m-d\TH:i:s.u\Z')) {
                        $data[$dateField] = \Carbon\Carbon::parse($data[$dateField])->format('Y-m-d H:i:s');
                    }
                }
            }
            
            // Forcer les timestamps au format MySQL
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            
            $promoteurs[] = $data;

            if (count($promoteurs) === $chunks) {
                \Illuminate\Support\Facades\DB::table('promoteurs')->insertOrIgnore($promoteurs);
                $promoteurs = [];
                echo "Promoteurs créés: " . min(($i + 1), $count) . "/{$count}\n";
            }
        }

        if (!empty($promoteurs)) {
            \Illuminate\Support\Facades\DB::table('promoteurs')->insertOrIgnore($promoteurs);
        }

        echo "Total promoteurs créés: {$count}\n";
    }
}
