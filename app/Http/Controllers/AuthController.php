<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ─── SHOW LOGIN PAGE ───────────────────────────────────────────────
    public function index()
    {
        $sections = [];

        try {
            if (\Schema::hasTable('sections')) {
                $sections = \App\Models\Section::orderBy('name')->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Unable to load sections for auth page.', [
                'message' => $e->getMessage(),
            ]);
        }

        return view('auth.index', compact('sections'));
    }

    // ─── LOGIN ─────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('lrn', $request->login_id)
                    ->orWhere('email', $request->login_id)
                    ->first();

        if (!$user) {
            return back()->with('error', 'Account not found!')->with('form', 'loginForm');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid password!')->with('form', 'loginForm');
        }

        // Save user to session
        session([
            'user_id'   => $user->id,
            'user_name' => $user->first_name,
            'user_role' => $user->role,
        ]);

        if ($user->role === 'teacher') {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        }

        return redirect('/');
    }

    // ─── REGISTER ──────────────────────────────────────────────────────
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lrn'              => [
                'required',
                'regex:/^[0-9]{12}$/',
                'unique:users,lrn',
            ],
            'section'          => 'required|string|max:100',
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'age'              => 'required|integer|min:1|max:120',
            'email'            => 'required|email|max:150|unique:users,email',
            'password'         => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ], [
            'lrn.required'     => 'LRN is required.',
            'lrn.regex'        => 'LRN must be exactly 12 numeric digits.',
            'lrn.unique'       => 'An account with this LRN already exists. Only one account per LRN is allowed.',
            'email.unique'     => 'An account with this email already exists.',
            'confirm_password.same' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('form', 'registerForm');
        }

        User::create([
            'lrn'        => trim($request->lrn),
            'section'    => $request->section,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'age'        => $request->age,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'student',
        ]);

        return redirect('/')->with('success', 'Registration successful! Please login.')
                             ->with('form', 'loginForm');
    }

    // ─── FORGOT PASSWORD ───────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'This email is not registered.')
                         ->with('form', 'forgotForm');
        }

        $token  = rand(100000, 999999);
        $expiry = Carbon::now('Asia/Manila')->addMinutes(10);

        // Save or update the reset record
        DB::table('resetpassword')->updateOrInsert(
            ['email' => $request->email],
            [
                'verification_code' => $token,
                'expires_at'        => $expiry,
                'used'              => 0,
                'updated_at'        => now(),
            ]
        );

        // Send mail
        Mail::send('emails.reset', ['token' => $token], function ($m) use ($request) {
            $m->to($request->email)
              ->subject('Password Reset Verification Code');
        });

        return redirect('/')->with('success', 'Verification code sent to your email!')
                             ->with('form', 'resetForm');
    }

    // ─── RESET PASSWORD ────────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'             => 'required|email',
            'verification_code' => 'required',
            'new_password'      => 'required|min:6',
            'confirm_password'  => 'required|same:new_password',
        ]);

        $record = DB::table('resetpassword')
            ->where('email', $request->email)
            ->where('verification_code', $request->verification_code)
            ->where('expires_at', '>', Carbon::now('Asia/Manila'))
            ->where('used', 0)
            ->first();

        if (!$record) {
            return back()->with('error', 'Invalid or expired verification code!')
                         ->with('form', 'resetForm');
        }

        // Update password
        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->new_password)]);


        // Mark token as used
        DB::table('resetpassword')
            ->where('email', $request->email)
            ->update(['used' => 1]);

        return redirect('/')->with('success', 'Password reset successful! You may now log in.')
                             ->with('form', 'loginForm');
    }

    // ─── LOGOUT ────────────────────────────────────────────────────────
    public function logout()
    {
        session()->flush();
        return redirect('/');
    }
}
