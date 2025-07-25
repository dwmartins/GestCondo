<?php

namespace Database\Seeders;

use App\Models\Condominium;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CondominiumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!Condominium::where('cnpj', '12.345.678/0001-90')->exists()) {
            $condo = Condominium::create([
                'name' => 'Condomínio Exemplo Residencial',
                'cnpj' => '12345678000190',
                'company_type' => 'horizontal',
                'postal_code' => '12345678',
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Jardim das Palmeiras',
                'city' => 'São Paulo',
                'state' => 'SP',
                'phone' => '11987654321',
                'email' => 'contato@exemplo.com',
                'expires_at' => now()->addYear(),
            ]);

            $condo->is_active = true;
            $condo->save();
        }
    }
}
