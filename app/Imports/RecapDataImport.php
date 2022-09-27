<?php

namespace App\Imports;

use App\Models\RecapData;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Http\Controllers\api\RecapDataController;

class RecapDataImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new RecapData([
            'created_by'=> auth()->user()->full_name,
            'claim_type'=> $row[0],
            'claim_name'=> $row[1],
            'claim_description'=> $row[2],
            'nominal'=> $row[3],
            'period_month'=> RecapDataController::toIntMonth($row[4]),
            'period_year'=> $row[5],
            'employee_id'=> $row[6],
            'created_at'=> date('Y-m-d H:i:s'),
            'updated_at'=> date('Y-m-d H:i:s'),
        ]);
    }
}
