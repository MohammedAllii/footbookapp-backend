<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CampiSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('data/campi.json')); 
        $campi = json_decode($json, true);

        foreach ($campi as $campo) {
            DB::table('campis')->insert([
                'nome' => $campo['nome'],
                'descrizione' => $campo['descrizione'],
                'tipo' => $campo['tipo'],
                'servizi' => json_encode($campo['servizi']),
                'email' => $campo['email'],
                'telefono' => $campo['telefono'],
                'recensione' => $campo['recensione'],
                'localita' => $campo['localita'],
                'foto' => json_encode($campo['foto']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
