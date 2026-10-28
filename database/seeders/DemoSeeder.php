<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local') || ! env('DEMO_PASSWORD')) {
            throw new \RuntimeException('DemoSeeder vereist APP_ENV=local en een eigen DEMO_PASSWORD.');
        }
        $user = User::firstOrCreate(['email' => 'demo@example.test'], ['name' => 'Robin de Vries', 'password' => env('DEMO_PASSWORD')]);
        $user->incomeSources()->firstOrCreate(['name' => 'Salaris'], ['amount' => '2850.00', 'frequency' => 'monthly', 'start_date' => now()->startOfYear()->toDateString(), 'is_active' => true]);
        foreach ([['Huur appartement', 'housing', '825.00', 'monthly', 1], ['Zorgverzekering', 'insurance', '146.50', 'monthly', 27],
            ['Internet thuis', 'telecom', '42.50', 'monthly', 15], ['Spotify', 'subscriptions', '10.99', 'monthly', 8],
            ['Sportschool', 'subscriptions', '29.95', 'monthly', 1], ['Fietsverzekering', 'transport', '96.00', 'yearly', null]] as [$name, $category, $amount, $frequency, $day]) {
            $user->fixedExpenses()->firstOrCreate(['name' => $name], ['category' => $category, 'amount' => $amount,
                'frequency' => $frequency, 'payment_day' => $day, 'start_date' => now()->startOfYear()->toDateString(), 'is_active' => true]);
        }
    }
}
