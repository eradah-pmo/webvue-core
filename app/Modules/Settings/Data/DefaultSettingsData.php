<?php

namespace App\Modules\Settings\Data;

class DefaultSettingsData
{
    /**
     * Get general system settings
     */
    public static function getGeneralSettings(): array
    {
        return [
            [
                'key' => 'app.name',
                'category' => 'general',
                'value' => 'WebVue Core Admin',
                'type' => 'string',
                'description' => 'Application name displayed throughout the system',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'app.description',
                'category' => 'general',
                'value' => 'Modern Laravel Admin Dashboard with Modular Architecture',
                'type' => 'string',
                'description' => 'Application description',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'app.logo',
                'category' => 'general',
                'value' => null,
                'type' => 'file',
                'description' => 'Application logo (recommended: 200x60px)',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'app.favicon',
                'category' => 'general',
                'value' => null,
                'type' => 'file',
                'description' => 'Application favicon (recommended: 32x32px)',
                'is_public' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'app.timezone',
                'category' => 'general',
                'value' => 'UTC',
                'type' => 'string',
                'description' => 'Default application timezone',
                'is_public' => true,
                'sort_order' => 5,
            ],
            [
                'key' => 'app.locale',
                'category' => 'general',
                'value' => 'en',
                'type' => 'string',
                'description' => 'Default application locale',
                'is_public' => true,
                'sort_order' => 6,
            ],
        ];
    }

    /**
     * Get authentication settings
     */
    public static function getAuthSettings(): array
    {
        return [
            [
                'key' => 'auth.session_timeout',
                'category' => 'authentication',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Session timeout in minutes',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'key' => 'auth.max_login_attempts',
                'category' => 'authentication',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Maximum login attempts before lockout',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'key' => 'auth.lockout_duration',
                'category' => 'authentication',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Lockout duration in minutes',
                'is_public' => false,
                'sort_order' => 3,
            ],
            [
                'key' => 'auth.password_min_length',
                'category' => 'authentication',
                'value' => '8',
                'type' => 'integer',
                'description' => 'Minimum password length',
                'is_public' => false,
                'sort_order' => 4,
            ],
            [
                'key' => 'auth.require_email_verification',
                'category' => 'authentication',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Require email verification for new accounts',
                'is_public' => false,
                'sort_order' => 5,
            ],
        ];
    }

    /**
     * Get email settings
     */
    public static function getEmailSettings(): array
    {
        return [
            [
                'key' => 'mail.from_name',
                'category' => 'email',
                'value' => 'WebVue Core Admin',
                'type' => 'string',
                'description' => 'Default sender name for emails',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'key' => 'mail.from_address',
                'category' => 'email',
                'value' => 'noreply@webvue.com',
                'type' => 'string',
                'description' => 'Default sender email address',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'key' => 'mail.admin_notifications',
                'category' => 'email',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Send admin notifications via email',
                'is_public' => false,
                'sort_order' => 3,
            ],
        ];
    }

    /**
     * Get security settings
     */
    public static function getSecuritySettings(): array
    {
        return [
            [
                'key' => 'security.audit_log_retention',
                'category' => 'security',
                'value' => '90',
                'type' => 'integer',
                'description' => 'Audit log retention period in days',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'key' => 'security.force_https',
                'category' => 'security',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Force HTTPS connections',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'key' => 'security.enable_2fa',
                'category' => 'security',
                'value' => false,
                'type' => 'boolean',
                'description' => 'Enable two-factor authentication',
                'is_public' => false,
                'sort_order' => 3,
            ],
        ];
    }

    /**
     * Get all default settings
     */
    public static function getAllSettings(): array
    {
        return array_merge(
            self::getGeneralSettings(),
            self::getAuthSettings(),
            self::getEmailSettings(),
            self::getSecuritySettings()
        );
    }
}
