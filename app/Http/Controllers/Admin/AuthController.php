<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Display Login Page
     */
    public function login()
    {
        return view('admin.auth.login');
    }

    /**
     * Login
     */
    public function loginPost(Request $request)
    {
        return $this->verifyLoginOtp($request);
    }

    /**
     * Send Login OTP
     */
    public function sendLoginOtp(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ]);

        $existingOtp = DB::table('login_otps')->where('email', $request->email)->first();
        if ($existingOtp && Carbon::parse($existingOtp->created_at)->addSeconds(60)->isFuture()) {
            $remainingSeconds = (int) ceil(Carbon::now()->diffInSeconds(Carbon::parse($existingOtp->created_at)->addSeconds(60)));

            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another OTP.',
                'resend_after' => $remainingSeconds,
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('login_otps')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($otp),
                'created_at' => Carbon::now(),
            ]
        );

        try {
            AppSetting::query()->first()?->applyMailConfiguration();
            Mail::raw("Your login OTP is {$otp}. This OTP expires in 3 minutes.", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Login OTP');
            });
        } catch (\Throwable $exception) {
            DB::table('login_otps')->where('email', $request->email)->delete();

            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP. Please check the Gmail App Password and try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'resend_after' => 60,
        ]);
    }

    /**
     * Verify Login OTP
     */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'otp' => 'required|digits:6',
        ]);

        $otpRecord = DB::table('login_otps')
            ->where('email', $request->email)
            ->first();

        if (! $otpRecord || Carbon::parse($otpRecord->created_at)->addMinutes(3)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new OTP.',
            ], 422);
        }

        if (! Hash::check($request->otp, $otpRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();
        DB::table('login_otps')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Welcome back!',
            'redirect' => route('admin.dashboard.index'),
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Profile
     */
    public function profile()
    {
        $user = Auth::user();

        return view('admin.auth.profile', compact('user'));
    }

    /**
     * Edit Profile
     */
    public function editProfile()
    {
        $user = Auth::user();

        return view('admin.auth.edit_profile', compact('user'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Clean prefix and mobile similar to UserController
        $prefix = trim((string) $request->input('mobile_number_prefix'));
        $prefix = preg_replace('/^\((\+\d{1,4})\)$/', '$1', $prefix);
        $mobileNumber = preg_replace('/\s+/', '', (string) $request->input('mobile_number'));

        $request->merge([
            'mobile_number_prefix' => $prefix ?: null,
            'mobile_number' => $mobileNumber ?: null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'mobile_number_prefix' => ['nullable', 'required_with:mobile_number', 'regex:/^\+\d{1,4}$/'],
            'mobile_number' => ['nullable', 'digits_between:1,12'],
            'address' => ['nullable', 'string', 'max:5000'],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'mobile_number.digits_between' => 'Contact number must contain 1 to 12 digits.',
            'profile_picture.max' => 'Profile image must not be greater than 2 MB.',
        ]);

        if ($request->hasFile('profile_picture')) {
            // Delete old image if it exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new image
            $path = $request->file('profile_picture')->store('users/profile_pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        // Only include prefix if user provided mobile, otherwise clear both
        if (empty($validated['mobile_number'])) {
            $validated['mobile_number_prefix'] = null;
            $validated['mobile_number'] = null;
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }
}
