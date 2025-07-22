<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use App\Helpers\AuditHelperSimple;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPasswordService
{
    /**
     * Reset user password with provided password
     */
    public function resetPassword(User $user, string $password): bool
    {
        $result = $user->update([
            'password' => Hash::make($password),
        ]);

        if ($result) {
            // Log the password reset action
            AuditHelperSimple::logAction(
                'user_password_reset',
                'User password was reset manually',
                'security',
                auth()->user(), // Admin who performed the action
                $user, // Target user
                'high', // High severity for security operations
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]
            );
        }

        return $result;
    }

    /**
     * Generate a random secure password
     */
    public function generateSecurePassword(int $length = 12): string
    {
        // Generate a secure password with a mix of letters, numbers, and symbols
        return Str::password(
            length: $length, 
            letters: true,
            numbers: true, 
            symbols: true, 
            spaces: false
        );
    }

    /**
     * Send password reset link to user email
     */
    public function sendPasswordResetLink(User $user): bool
    {
        // This would send a password reset link to the user
        // Leveraging Laravel's built-in password reset functionality
        
        // For now we'll implement a basic log
        AuditHelperSimple::logAction(
            'password_reset_link_sent',
            'Password reset link was sent to user',
            'security',
            auth()->user(),
            $user,
            'medium',
            [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]
        );

        // Here you would call the actual password broker to send reset link
        // Example: Password::sendResetLink(['email' => $user->email]);

        // Placeholder return since we're not actually sending emails in this example
        return true;
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $strength = 0;
        $feedback = [];
        
        // Length check
        if (strlen($password) < 8) {
            $feedback[] = 'Password should be at least 8 characters long';
        } else {
            $strength += 1;
        }
        
        // Uppercase check
        if (!preg_match('/[A-Z]/', $password)) {
            $feedback[] = 'Password should include at least one uppercase letter';
        } else {
            $strength += 1;
        }
        
        // Lowercase check
        if (!preg_match('/[a-z]/', $password)) {
            $feedback[] = 'Password should include at least one lowercase letter';
        } else {
            $strength += 1;
        }
        
        // Number check
        if (!preg_match('/[0-9]/', $password)) {
            $feedback[] = 'Password should include at least one number';
        } else {
            $strength += 1;
        }
        
        // Special character check
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $feedback[] = 'Password should include at least one special character';
        } else {
            $strength += 1;
        }
        
        return [
            'strength' => $strength,
            'feedback' => $feedback,
            'is_valid' => $strength >= 3 && strlen($password) >= 8,
        ];
    }
}
