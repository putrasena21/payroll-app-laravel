<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Imports\RecapDataImport;
use App\Models\RecapData;
use App\Models\UserEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class RecapDataController extends Controller
{
    public static function toIntMonth($strMonth)
    {
        $month = [
            'JANUARY' => 1,
            'FEBRUARY' => 2,
            'MARCH' => 3,
            'APRIL' => 4,
            'MAY' => 5,
            'JUNE' => 6,
            'JULY' => 7,
            'AUGUST' => 8,
            'SEPTEMBER' => 9,
            'OCTOBER' => 10,
            'NOVEMBER' => 11,
            'DECEMBER' => 12
        ];

        return $month[strtoupper($strMonth)];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validated = Validator::make($request->all(),[
            'claim_type' => 'required',
            'claim_name' => 'required',
            'claim_description' => 'required',
            'nominal' => 'required',
            'period_month' => 'required',
            'period_year' => 'required',
            'employee_id'=> 'required',
        ]);

        if ($validated->fails()) {
            return ResponseFormatter::createResponse(
                400,
                'Failed to create new recap data',
                ['errors' => $validated->errors()->all()]
            );
        }

        $claim_options = ['HEALTH', 'WELLNESS', 'TAX', 'DEDUCTION'];

        if(!in_array($request->claim_type, $claim_options)){
            return ResponseFormatter::createResponse(
                400,
                'Failed to create new recap data',
                ['errors' => 'Claim type must be HEALTH, WELLNESS, TAX, or DEDUCTION']
            );
        }

        $employee = UserEmployee::find($request->employee_id);
        if(! $employee) {
            return ResponseFormatter::createResponse(
                400,
                'Failed to create new recap data',
                ['errors' => 'Employee not found']
            );
        }

        // check if remaining balance is enough
        $remaining = (float) $employee->salary;
        $nominal = (float) $request->nominal;
        if($request->claim_type == 'HEALTH' || $request->claim_type == 'WELLNESS'){
            $remaining = $remaining - $nominal;
        }

        $claim_histories = RecapData::where('employee_id', $request->employee_id)
            ->where('period_year', $request->period_year);

        foreach($claim_histories->get() as $claim_history){
            if($claim_history->claim_type == 'HEALTH' || $claim_history->claim_type == 'WELLNESS'){
                $history_nominal = (float) $claim_history->nominal;
                $remaining = $remaining - $history_nominal;
            }
        }

        if($remaining < 0){
            return ResponseFormatter::createResponse(
                400,
                'Failed to create new recap data',
                ['errors' => 'Remaining balance is not enough']
            );
        }

        $recapData = RecapData::create([
            'created_by' => auth()->user()->full_name,
            'claim_type' => $request->claim_type,
            'claim_name' => $request->claim_name,
            'claim_description' => $request->claim_description,
            'nominal' => $request->nominal,
            'period_month' => self::toIntMonth($request->period_month),
            'period_year' => $request->period_year,
            'employee_id' => $request->employee_id,
        ]);

        return ResponseFormatter::createResponse(
            200,
            'Success create new recap data',
            $recapData
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RecapData  $recapData
     * @return \Illuminate\Http\Response
     */
    public function show(RecapData $recapData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RecapData  $recapData
     * @return \Illuminate\Http\Response
     */
    public function edit(RecapData $recapData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RecapData  $recapData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RecapData $recapData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RecapData  $recapData
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecapData $recapData)
    {
        //
    }

    public function importExcel(Request $request)
    {
        $validated = Validator::make($request->all(),[
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        if ($validated->fails()) {
            return ResponseFormatter::createResponse(
                400,
                'Failed to import recap data',
                ['errors' => $validated->errors()->all()]
            );
        }

        $file = $request->file('file');
        $fileName = rand().$file->getClientOriginalName();
        $file->move('uploads', $fileName);

        Excel::import(new RecapDataImport, public_path('uploads/'.$fileName));

        return ResponseFormatter::createResponse(
            200,
            'Success import recap data',
        );
    }
}
