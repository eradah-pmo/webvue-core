<?php

return [
    'title' => 'Settings',
    'description' => 'Manage system settings and configuration',
    'add' => 'Add Setting',
    'create' => 'Create Setting',
    'edit' => 'Edit Setting',
    'create_description' => 'Create a new system setting',
    'edit_description' => 'Edit existing system setting',
    'no_settings' => 'No settings found',
    'no_settings_description' => 'Get started by creating your first setting.',
    'add_first' => 'Add your first setting',
    'all_categories' => 'All Categories',
    
    // Form fields
    'key' => 'Key',
    'key_help' => 'Unique identifier for this setting (e.g., app.name)',
    'display_name' => 'Display Name',
    'display_name_placeholder' => 'Human-readable name for this setting',
    'description' => 'Description',
    'description_placeholder' => 'Brief description of what this setting controls',
    'value' => 'Value',
    'value_placeholder' => 'Setting value',
    'type' => 'Type',
    'category' => 'Category',
    'current_value' => 'Current Value',
    'is_public' => 'Public Setting',
    'is_public_help' => 'Public settings can be accessed by frontend applications',
    
    // Types
    'types' => [
        'text' => 'Text',
        'number' => 'Number',
        'boolean' => 'Boolean',
        'file' => 'File',
        'password' => 'Password',
    ],
    
    // Categories
    'categories' => [
        'general' => 'General',
        'appearance' => 'Appearance',
        'security' => 'Security',
        'email' => 'Email',
        'api' => 'API',
        'system' => 'System',
    ],
    
    // Placeholders and help text
    'boolean_help' => 'Enable this setting',
    'password_placeholder' => 'Enter password value',
    'number_placeholder' => 'Enter numeric value',
    'current_file' => 'Current file',
    
    // Messages
    'created_successfully' => 'Setting created successfully',
    'updated_successfully' => 'Setting updated successfully',
    'deleted_successfully' => 'Setting deleted successfully',
    'creation_failed' => 'Failed to create setting',
    'update_failed' => 'Failed to update setting',
    'deletion_failed' => 'Failed to delete setting',
];
