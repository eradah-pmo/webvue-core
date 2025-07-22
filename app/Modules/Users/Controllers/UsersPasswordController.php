<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Services\UserPasswordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersPasswordController extends Controller
{
    /**
     * The password service instance.
     */
    private UserPasswordService $passwordService;

    /**
     * Create a new controller instance.
     */
    public function __construct(UserPasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('users.manage');

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check password strength
        $strength = $this->passwordService->validatePasswordStrength($request->password);
        
        if (!$strength['is_valid']) {
            return redirect()->back()
                ->withErrors(['password' => $strength['feedback']])
                ->withInput();
        }

        $this->passwordService->resetPassword($user, $request->password);
        
        return redirect()->back()->with('success', 'Password reset successfully');
    }

    /**
     * Generate a random secure password for a user.
     */
    public function generatePassword(Request $request)
    {
        $this->authorize('users.manage');
        
        $length = $request->get('length', 12);
        $password = $this->passwordService->generateSecurePassword($length);
        
        return response()->json([
            'password' => $password
        ]);
    }

    /**
     * Send password reset link to user.
     */
    public function sendResetLink(Request $request, User $user)
    {
        $this->authorize('users.manage');
        
        $this->passwordService->sendPasswordResetLink($user);
        
        return redirect()->back()->with('success', 'Password reset link sent to user');
    }
}
