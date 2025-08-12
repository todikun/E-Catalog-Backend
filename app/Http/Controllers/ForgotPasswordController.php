<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

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
            ], 404);
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

            if ($request->password !== $request->confirm_password) {
                return response()->json(['message' => 'Password and Confirm Password do not match'], 400);
            }

            // Decode token to get user ID
            $payload = JWTAuth::setToken($request->token)->getPayload();
            $userId = $payload->get('sub'); // usually the user ID

            if (!$userId) {
                return response()->json(['message' => 'Token missing user ID'], 400);
            }

            // Find user by ID
            $account = Accounts::find($userId);

            if (!$account || strcasecmp($account->username, $request->email) !== 0) {
                return response()->json(['message' => 'Invalid token or email'], 400);
            }

            // Update password
            $account->password = bcrypt($request->password);
            $account->save();

            return response()->json(['message' => 'Password successfully reset']);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not reset password'], 500);
        }
    }
}
