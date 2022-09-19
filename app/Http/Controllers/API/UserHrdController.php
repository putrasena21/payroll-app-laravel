<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\UserHrd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class UserHrdController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = UserHrd::query();

        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        if ($request->has('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->has('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        if($request->has('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        if($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
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
            'username' => 'required|string|min:4|unique:user_hrds,username',
            'full_name' => 'required|string|min:3',
            'email' => 'required|email|unique:user_hrds,email',
            'password' => ['required','string', Password::min(8)->mixedCase()->numbers()],
        ]);

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        $newHrd = UserHrd::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return ResponseFormatter::createResponse(201, 'success', $newHrd);
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
     * @param  \App\Models\UserHrd  $userHrd
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $hrd = UserHrd::find($id);

        if(! $hrd){
            return ResponseFormatter::createResponse(404, 'Hrd not found');
        }

        return ResponseFormatter::createResponse(200, 'Success get hrd', $hrd);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserHrd  $userHrd
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required',
            'username' => 'required|string|min:4|unique:user_hrds,username',
            'full_name' => 'required|string|min:3',
            'email' => 'required|email|unique:user_hrds,email',
        ]);

        $hrd = UserHrd::find($request->id);

        if(! $hrd){
            return ResponseFormatter::createResponse(404, 'Hrd not found');
        }

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        $hrd->username = $request->username;
        $hrd->full_name = $request->full_name;
        $hrd->email = $request->email;
        $hrd->save();

        $response = [
            'id' => $hrd->id,
            'username' => $hrd->username,
            'full_name' => $hrd->full_name,
            'email' => $hrd->email,
        ];

        return ResponseFormatter::createResponse(200, 'Success update hrd', $response);
    }

    public function editPassword(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required',
            'oldPassword' => ['required','string', Password::min(8)->mixedCase()->numbers()],
            'newPassword' => ['required','string', Password::min(8)->mixedCase()->numbers()],
        ]);

        $hrd = UserHrd::find($request->id);

        if(! $hrd){
            return ResponseFormatter::createResponse(404, 'Hrd not found');
        }

        if(! Hash::check($request->oldPassword, $hrd->password)) {
            return ResponseFormatter::createResponse(400, 'Password not match');
        }

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        $hrd->password = bcrypt($request->newPassword);
        $hrd->save();

        $response = [
            'id' => $hrd->id,
            'username' => $hrd->username,
            'full_name' => $hrd->full_name,
            'email' => $hrd->email,
        ];

        return ResponseFormatter::createResponse(200, 'Success update password hrd', $response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserHrd  $userHrd
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserHrd $userHrd)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserHrd  $userHrd
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $validated = Validator::make(['id' => $id], [
            'id'=> 'required',
        ]);

        $employee = UserHrd::find($id);

        if(! $employee){
            return ResponseFormatter::createResponse(404, 'Employee not found');
        }

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        UserHrd::where('id', $id)->update([
            'status' => 'DELETED',
        ]);

        $response = [
            'id' => $employee->id,
            'username' => $employee->username,
            'full_name' => $employee->full_name,
            'email' => $employee->email,
            'status' => 'DELETED',
        ];

        return ResponseFormatter::createResponse(200, 'Success delete user employee', $response);
    }
}
