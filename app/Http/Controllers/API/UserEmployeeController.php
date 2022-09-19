<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\UserEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = UserEmployee::query();

        if ($request->has('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        if($request->has('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        $size = $request->input('size', 10);
        $page = $request->input('page', 1);
        $totalData = $query->count();
        $totalPages = ceil($totalData / $size);

        if($page > $totalPages) {
            $page = $totalPages;
        }

        $result = $query->offset(($page - 1) * $size)->limit($size)->get();

        return [
            'statusCode' => 200,
            'success' => true,
            'message' => 'List of all user employees',
            'content' => $result,
            'totalData' => (int) $totalData,
            'totalPages' => (int) $totalPages,
            'page' => (int)$page,
            'size' => (int) $size,
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'full_name' => 'required|string|min:3',
            'email' => 'required|email|unique:user_employees,email',
            'salary' => 'required',
        ]);

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        $newEmployee = UserEmployee::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'salary' => $request->salary,
            'status' => 'ACTIVE',
        ]);

        $response = [
            'id' => $newEmployee->id,
            'full_name' => $newEmployee->full_name,
            'email' => $newEmployee->email,
            'salary' => $newEmployee->salary,
            'status' => $newEmployee->status,
        ];

        return ResponseFormatter::createResponse(200, 'Success', $response);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = UserEmployee::find($id);

        if(! $employee){
            return ResponseFormatter::createResponse(404, 'Employee not found');
        }

        return ResponseFormatter::createResponse(200, 'Success get employee', $employee);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id'=> 'required',
            'full_name' => 'string|min:3',
            'email' => 'email|unique:user_employees,email',
        ]);

        $employee = UserEmployee::find($request->id);

        if(! $employee){
            return ResponseFormatter::createResponse(404, 'Employee not found');
        }

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        $employee->full_name = $request->full_name;
        $employee->email = $request->email;
        $employee->salary = $request->salary;
        $employee->save();

        $response = [
            'id' => $employee->id,
            'full_name' => $employee->full_name,
            'email' => $employee->email,
            'salary' => $employee->salary,
            'status' => $employee->status,
        ];

        return ResponseFormatter::createResponse(200, 'Success edit user employee', $response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $validated = Validator::make(['id' => $id], [
            'id'=> 'required',
        ]);

        $employee = UserEmployee::find($id);

        if(! $employee){
            return ResponseFormatter::createResponse(404, 'Employee not found');
        }

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        UserEmployee::where('id', $id)->update([
            'status' => 'DELETED',
        ]);

        $response = [
            'id' => $employee->id,
            'full_name' => $employee->full_name,
            'email' => $employee->email,
            'status' => 'DELETED',
        ];

        return ResponseFormatter::createResponse(200, 'Success delete user employee', $response);
    }
}
