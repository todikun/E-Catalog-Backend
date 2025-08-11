<?php

namespace App\Http\Controllers;

use App\Mail\SendUsernameAndPassword;
use App\Services\UserService;
use App\Services\LoginService;
use App\Models\Accounts;
use App\Models\Users;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AccountController extends Controller
{
    protected $userService;
    protected $loginService;

    public function __construct(
        UserService $userService,
        LoginService $loginService
    ) {
        $this->userService = $userService;
        $this->loginService = $loginService;
    }

    public function sendUsernameAndEmail(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'role_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'data' => []
            ],400);
        }

        $checkUserId = $this->userService->checkUserIfExist($request['user_id']);

        if (!$checkUserId) {
            return response()->json([
                'status' => 'error',
                'message' => 'user id ' . $request['user_id'] . ' tidak dapat ditemukan!',
                'data' => []
            ],404);
        }

        try {
            $generatePassword = (string) Str::random(5);

            $accounts = Accounts::create([
                'user_id' => $checkUserId['id'],
                'username' => $checkUserId['email'],
                'password' =>  Hash::make($generatePassword),
            ]);

            $dataUser = Users::where('id', $request['user_id'])
                ->update([
                    'id_roles' => $request['role_id'],
                    'status' => 'active',
                ]);

            if ($accounts && $dataUser) {

                Mail::to($checkUserId['email'])->send(new SendUsernameAndPassword($checkUserId['email'], $generatePassword));

                return response()->json([
                    'status' => 'success',
                    'message' => 'Accounts berhasil disimpan',
                    'data' => $accounts
                ]);
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan accounts!',
                'error' => $th->getMessage()
            ],500);
        }
    }
}
