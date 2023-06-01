<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            "first_name" => ["bail", "required", "string"],
            "last_name" => ["bail", "required", "string"],
            "dob" => ["bail", "required", "date_format:Y-m-d"],
            "email" => ["bail", "required", "email"],
            "timezone" => ["bail", "required"],
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return response(["code" => "42200", "message" => $validator->errors()->first()], 422);
        }

        $validated = $validator->safe();

        $user = User::create([
            "first_name" => $validated->first_name,
            "last_name" => $validated->last_name,
            "dob" => $validated->dob,
            "email" => $validated->email,
            "timezone" => $validated->timezone,
        ])->fresh();

        if($user){
            return response(["code" => "20000", "message" => "OK", "data" => $user], 200);
        }

        return response(["code" => "40000", "message" => "Server busy"], 400);
    }

    public function update($id, Request $request){
        $validator = Validator::make($request->all(), [
            "first_name" => ["bail", "required", "string"],
            "last_name" => ["bail", "required", "string"],
            "dob" => ["bail", "required", "date_format:Y-m-d"],
            "email" => ["bail", "required", "email"],
            "timezone" => ["bail", "required"],
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return response(["code" => "42200", "message" => $validator->errors()->first()], 422);
        }

        $validated = $validator->safe();

        $user = User::find($id);
        if(!$user){
            return response(["code" => "40001", "message" => "User not found"], 400);
        }

        $user->update([
            "first_name" => $validated->first_name,
            "last_name" => $validated->last_name,
            "dob" => $validated->dob,
            "email" => $validated->email,
            "timezone" => $validated->timezone,
        ]);

        if($user){
            return response(["code" => "20000", "message" => "OK", "data" => $user], 200);
        }

        return response(["code" => "40000", "message" => "Server busy"], 400);
    }

    public function delete($id){
        $user = User::find($id);
        if($user){
            $user->delete();
            return response(["code" => "20000", "message" => "Deleted"], 200);
        }

        return response(["code" => "40000", "message" => "Server busy"], 400);
    }
}
