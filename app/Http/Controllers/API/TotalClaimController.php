<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\RecapData;
use App\Models\UserEmployee;
use Illuminate\Http\Request;

class TotalClaimController extends Controller
{
    public static function toStrMonth($intMonth)
    {
        $month = [
            1 => 'JANUARY',
            2 => 'FEBRUARY',
            3 => 'MARCH',
            4 => 'APRIL',
            5 => 'MAY',
            6 => 'JUNE',
            7 => 'JULY',
            8 => 'AUGUST',
            9 => 'SEPTEMBER',
            10 => 'OCTOBER',
            11 => 'NOVEMBER',
            12 => 'DECEMBER'
        ];

        return $month[$intMonth];
    }

    public function totalClaim(Request $request)
    {
        $employee = UserEmployee::find($request->employee_id);

        if($request->period_start > $request->period_end) {
            return response()->json([
                'message' => 'Period start must be less than period end'
            ], 400);
        }

        // sum nominal claim and group by employee_id
        $totalClaim = RecapData::where('employee_id', $employee->id)
            ->whereBetween('period_month', [$request->period_start, $request->period_end])
            ->where('period_year', $request->period_year)
            ->sum('nominal');


        $dto = [
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'salary' => (float) $employee->salary,
            'period_start' => self::toStrMonth($request->period_start),
            'period_end' => self::toStrMonth($request->period_end),
            'period_year' => $request->period_year,
            'remaining_claim' => $employee->salary - $totalClaim,
            'total_claim' => (float) $totalClaim
        ];

        return ResponseFormatter::createResponse(200, 'Success', $dto);
    }
}
