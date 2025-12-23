<?php

namespace Database\Seeders;

use App\Models\Recruiter;
use Illuminate\Database\Seeder;

class RecruiterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recruiters = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@recruitment.com',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'email' => 'michael.chen@recruitment.com',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Rodriguez',
                'email' => 'emily.rodriguez@recruitment.com',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Williams',
                'email' => 'david.williams@recruitment.com',
            ],
            [
                'first_name' => 'Jessica',
                'last_name' => 'Martinez',
                'email' => 'jessica.martinez@recruitment.com',
            ],
        ];

        foreach ($recruiters as $recruiter) {
            Recruiter::create($recruiter);
        }
    }
}
