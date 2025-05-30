<?php

namespace Database\Seeders;

use App\Models\VendingMachine;
use Illuminate\Database\Seeder;

class VendingMachineSeeder extends Seeder
{
    public function run(): void
    {
        $machines = [
            [
                'name' => 'Vending Machine 1',
                'token' => 'ABC123',
                'location' => 'Lobby',
                'topic' => 'vending/machine1/orders'
            ],
            [
                'name' => 'Vending Machine 2',
                'token' => 'DEF456',
                'location' => 'Cafeteria',
                'topic' => 'vending/machine2/orders'
            ],
            [
                'name' => 'Vending Machine 3',
                'token' => 'GHI789',
                'location' => 'Office Area',
                'topic' => 'vending/machine3/orders'
            ]
        ];

        foreach ($machines as $machine) {
            VendingMachine::create($machine);
        }
    }
} 