<?php

return [
    // General
    'title' => 'Settings',
    'settings' => 'Settings',
    'setting' => 'Setting',
    'manage_settings' => 'Manage Settings',
    'system_settings' => 'System Settings',
    
    // Actions
    'create_setting' => 'Create Setting',
    'edit_setting' => 'Edit Setting',
    'delete_setting' => 'Delete Setting',
    'save_setting' => 'Save Setting',
    'update_setting' => 'Update Setting',
    'clear_cache' => 'Clear Cache',
    'update_multiple' => 'Update Multiple',
    
    // Fields
    'key' => 'Key',
    'category' => 'Category',
    'value' => 'Value',
    'type' => 'Type',
    'description' => 'Description',
    'validation_rules' => 'Validation Rules',
    'options' => 'Options',
    'is_public' => 'Public',
    'is_encrypted' => 'Encrypted',
    'sort_order' => 'Sort Order',
    'active' => 'Active',
    
    // Categories
    'general' => 'General',
    'security' => 'Security',
    'mail' => 'Email',
    'ui' => 'User Interface',
    'files' => 'Files',
    'notifications' => 'Notifications',
    'backup' => 'Backup',
    
    // Types
    'string' => 'Text',
    'number' => 'Number',
    'boolean' => 'Yes/No',
    'json' => 'JSON',
    'file' => 'File',
    
    // Messages
    'created_successfully' => 'Setting created successfully',
    'updated_successfully' => 'Setting updated successfully',
    'deleted_successfully' => 'Setting deleted successfully',
    'creation_failed' => 'Failed to create setting',
    'update_failed' => 'Failed to update setting',
    'deletion_failed' => 'Failed to delete setting',
    'cache_cleared' => 'Settings cache cleared successfully',
    'cache_clear_failed' => 'Failed to clear settings cache',
    
    // Validation
    'key_required' => 'Setting key is required',
    'key_unique' => 'Setting key must be unique',
    'key_format' => 'Setting key must contain only lowercase letters, numbers, underscores, and dots',
    'category_required' => 'Category is required',
    'type_required' => 'Type is required',
    'type_invalid' => 'Invalid setting type',
    'file_too_large' => 'File size must not exceed 10MB',
    
    // Help Text
    'key_help' => 'Unique identifier for this setting (e.g., app.name)',
    'category_help' => 'Group settings by category for better organization',
    'type_help' => 'Data type determines how the value is stored and validated',
    'public_help' => 'Public settings can be accessed by frontend applications',
    'encrypted_help' => 'Encrypted settings are stored securely in the database',
    'sort_order_help' => 'Lower numbers appear first in listings',
    
    // Placeholders
    'search_settings' => 'Search settings...',
    'select_category' => 'Select Category',
    'select_type' => 'Select Type',
    'enter_value' => 'Enter value',
    'enter_description' => 'Enter description',
];
