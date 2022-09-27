<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\RecapData;
use App\Models\UserEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use stdClass;

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

    public function recapClaim (Request $request)
    {
        $validated = Validator::make($request->all(), [
            'employee_id' => 'exists:user_employees,id',
            'period_start' => 'numeric|between:1,12',
            'period_end' => 'numeric|between:1,12',
            'period_year' => 'required|numeric'
        ]);

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        $recapQuery = RecapData::query();

        if($request->has('employee_id')) {
            $recapQuery->where('employee_id', $request->employee_id);
        }

        if($request->has('period_start') && $request->has('period_end')) {
            $recapQuery->whereBetween('period_month', [$request->period_start, $request->period_end]);
        }

        if($request->has('period_year')) {
            $recapQuery->where('period_year', $request->period_year);
        }

        // group by employee_id, period_month, period_year, claim_type
        $recapData = $recapQuery->selectRaw('employee_id, created_by, period_month, period_year, claim_type, sum(nominal) as total_claim')
            ->groupBy('employee_id', 'created_by','period_month', 'period_year', 'claim_type')
            ->orderBy('employee_id', 'asc')
            ->orderBy('period_year', 'asc')
            ->orderBy('period_month', 'asc')
            ->get();;

        $employee = UserEmployee::all();
        $dataEmployee = $employee->map(function ($item) {
            return [
                'employee_id' => $item->id,
                'employee_name' => $item->full_name,
                'salary' => (float) $item->salary,
                'salary_constans' => (float) $item->salary
            ];
        });

        $findedUser = [];
        foreach($recapData as $item) {
            if(! in_array($item->employee_id, $findedUser)) {
                $findedUser[] = $item->employee_id;
            }
        }

        $populate = array();
        $recapData->map(function ($item) use (&$employee, &$populate, &$dataEmployee) {
            $indexEmployee = $employee->search(function ($value) use ($item) {
                return $value->id === $item->employee_id;
            });

            // search employee id && period month && period year
            $isUserExist = Arr::where($populate, function ($value, $key) use ($item) {
                return $value['employee_id'] === $item->employee_id && $value['period_month'] === $item->period_month && $value['period_year'] === $item->period_year;
            });

            if(! $isUserExist) {
                // push into $populate
                $populate[] = [
                    'employee_id' => $item->employee_id,
                    'period_month' => $item->period_month,
                    'period_year' => $item->period_year,
                ];
            }

            // find index of populate
            $userIndex = array_search($item->employee_id, array_column($populate, 'employee_id'));

            $currentUserPopulate = $populate[$userIndex];

            $totalReimburse = $currentUserPopulate->total_reimburse ?? 0;
            $tax = $currentUserPopulate->tax ?? 0;
            $deduction = $currentUserPopulate->deduction ?? 0;

            if($item->claim_type == 'WELLNESS' || $item->claim_type == 'HEALTH'){
                // renew wellness and health
                $totalReimburse = $totalReimburse + $item->total_claim;
            }

            if($item->claim_type == 'TAX'){
                $tax = $tax + $item->total_claim;
            }

            if($item->claim_type == 'DEDUCTION'){
                $deduction = $deduction + $item->total_claim;
            }

            $dto = [
                'employee_id' => $item->employee_id,
                'employee_name' => $dataEmployee[$indexEmployee]['employee_name'],
                'period_month' => $item->period_month,
                'period_year' => $item->period_year,
                'salary' => (float) $dataEmployee[$indexEmployee]['salary_constans'],
                'reimbursement' => (float) $totalReimburse,
                'tax' => (float) $tax,
                'deduction' => (float) $deduction,
                'remaining_claim_limit' => (float) $dataEmployee[$indexEmployee]['salary'] - $totalReimburse,
                'total_salary' => (float) $dataEmployee[$indexEmployee]['salary'] + $totalReimburse - $tax - $deduction,
                'recap_date' => Date('Y-m-d H:i:s')
            ];

            $populate[$userIndex] = $dto;
        });

        return ResponseFormatter::createResponse(200, 'Success', $populate);
    }
}
