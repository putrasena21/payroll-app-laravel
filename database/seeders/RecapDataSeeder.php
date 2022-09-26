<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecapDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 1; $i <= 10; $i++){

            $hrd = 'John Doe';
            $claim_type = ['WELLNESS', 'HEALTH', 'TAX', 'DEDUCTION'];

            DB::table('recap_data')->insert([
                'created_by' => $hrd,
                'claim_type' => $claim_type[rand(0, 3)],
                'claim_name' => 'Claim Name ' . $i,
                'claim_description' => 'Claim Description ' . $i,
                'nominal' => 100000,
                'period_month' => 9,
                'period_year' => 2022,
                'employee_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]);
        }
    }
}
