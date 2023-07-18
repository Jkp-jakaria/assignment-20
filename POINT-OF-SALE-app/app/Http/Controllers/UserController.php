<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function UserRegistration(Request $request)
    {
        try {
            User::create([
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => $request->input('password'),
            ]);

            return response()->json([
                'status' => 'Success',
                'message' => 'User Registration Successfull',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Failed',
                'message' => 'User Registration Failed',
            ], 200);
        }
    }

    public function UserLogin(Request $request)
    {
        $count = User::where('email', '=', $request->input('email'))->where('password', '=', $request->input('password'))->count();

        if ($count == 1) {
            // User Login-> JWT Token Issue
            $token = JWTToken::CreateToken($request->input('email'));

            return response()->json([
                'status' => 'Success',
                'message' => 'User Login Successfull',
                'token' => $token,
            ], 200);
        } else {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Unauthorized',
            ], 200);
        }
    }

    public function SendOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $count = User::where('email', '=', $email)->count();

        if ($count == 1) {
            //OTP Email Address
            Mail::to($email)->send(new OTPMail($otp));

            //OTP Code Update
            User::where('email', '=', $email)->Update(['otp' => $otp]);

            return response()->json([
                'status' => 'Success',
                'message' => '4 Digit OTP Code has been send to your email !',
            ], 200);
        } else {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Unauthorized',
            ], 200);
        }

    }

    public function VerifyOTP(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', '=', $email)->where('otp', '=', $otp)->count();

        if ($count == 1) {
            //Database OTP Update
            User::where('email', '=', $email)->Update(['otp' => '0']);

            //Pass Reset Token Issue
            $token = JWTToken::CreateTokenForSetPassword($request->input('email'));

            return response()->json([
                'status' => 'Success',
                'message' => 'OTP Verification Successfull',
                'token' => $token,
            ], 200);
        } else {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Unauthorized',
            ], 200);
        }
    }

    public function ResetPass(Request $request)
    {
        try {
            $email = $request->header('email');
            $password = $request->input('password');
            User::where('email', '=', $email)->Update(['password' => $password]);

            return response()->json([
                'status' => 'Success',
                'message' => 'Password Reset Successfull',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Something Went Wrong',
            ], 200);
        }
    }
}
