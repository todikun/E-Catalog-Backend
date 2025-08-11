<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $users = Accounts::where('username', $request->email)->first();

        if (!$users) {
            return response()->json([
                'status' => 'error',
                'message' => 'user tidak ditemukan!',
                'data' => []
            ]);
        }

        $token = JWTAuth::fromUser($users);

        try {
            //$users->notify(new ResetPasswordNorification($token));
            Mail::to($users->username)->send(new ResetPasswordMail($token, $users->username));
        } catch (\Exception $e) {
            Log::error('Error sending password reset notification: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email'], 500);
        }

        return response()->json(['message' => 'Reset password link sent']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required',
        ]);

        try {
            $users = JWTAuth::authenticate($request->token);

            if (!$users || $users->email !== $request->email) {
                return response()->json(['message' => 'Invalid token or email'], 400);
            }

            $users->password = bcrypt($request->password);
            $users->save();

            return response()->json(['message' => 'Password successfully reset']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not reset password'], 500);
        }
    }
}
