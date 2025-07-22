import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function TestNavigation({ user, permissions = [], directPermissions = [], roles = [], accessibleDepartments = [], manageableUsers = 0, message, systemInfo = {} }) {
    const testLinks = [
        { name: 'Dashboard', href: '/dashboard', permission: null },
        { name: 'Users', href: '/users', permission: 'users.view' },
        { name: 'Roles', href: '/roles', permission: 'roles.view' },
        { name: 'Departments', href: '/departments', permission: 'departments.view' },
        { name: 'Settings', href: '/settings', permission: 'settings.view' },
    ];

    const hasPermission = (permission) => {
        if (!permission) return true; // No permission required
        return permissions.includes(permission);
    };

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title="Navigation Test" />
            
            <div className="max-w-4xl mx-auto py-6 px-4">
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        🔍 صفحة تشخيص التوجيه
                    </h1>
                    
                    {/* Success Message */}
                    <div className="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                        <div className="flex">
                            <div className="flex-shrink-0">
                                <span className="text-green-400 text-xl">✅</span>
                            </div>
                            <div className="ml-3">
                                <p className="text-sm text-green-800">
                                    {message || 'Navigation test page loaded successfully!'}
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* User Info */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div className="bg-gray-50 rounded-lg p-4">
                            <h3 className="text-lg font-medium text-gray-900 mb-3">
                                User Information
                            </h3>
                            <div className="space-y-2 text-sm">
                                <p><strong>Name:</strong> {user?.name || 'N/A'}</p>
                                <p><strong>Email:</strong> {user?.email || 'N/A'}</p>
                                <p><strong>Status:</strong> {user?.is_active ? 'Active' : 'Inactive'}</p>
                            </div>
                        </div>

                        <div className="bg-gray-50 rounded-lg p-4">
                            <h3 className="text-lg font-medium text-gray-900 mb-3">
                                Roles & Permissions
                            </h3>
                            <div className="space-y-2 text-sm">
                                <p><strong>Roles:</strong> {roles.join(', ') || 'None'}</p>
                                <p><strong>Role-based Permissions:</strong> {systemInfo.roleBasedPermissions || permissions.length}</p>
                                <p><strong>Direct Permissions:</strong> {systemInfo.directPermissions || directPermissions.length} (should be 0)</p>
                                <p><strong>Total Roles:</strong> {systemInfo.totalRoles || roles.length}</p>
                            </div>
                        </div>
                    </div>

                    {/* New Permission System Info */}
                    <div className="mb-8">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">
                            🔧 New Permission System Status
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div className="bg-blue-50 rounded-lg p-4">
                                <h4 className="font-medium text-blue-900 mb-2">System Tests</h4>
                                <div className="space-y-1 text-sm">
                                    <p>Can View Users: {systemInfo.canViewUsers ? '✅ Yes' : '❌ No'}</p>
                                    <p>Can Delete Users: {systemInfo.canDeleteUsers ? '✅ Yes' : '❌ No'}</p>
                                    <p>Direct Permissions: {systemInfo.directPermissions === 0 ? '✅ Removed' : '❌ Still Present'}</p>
                                </div>
                            </div>
                            <div className="bg-green-50 rounded-lg p-4">
                                <h4 className="font-medium text-green-900 mb-2">Department Scope</h4>
                                <div className="space-y-1 text-sm">
                                    <p>Accessible Departments: {accessibleDepartments.length}</p>
                                    <p>Manageable Users: {manageableUsers}</p>
                                    <p>User Department: {user?.department?.name || 'None'}</p>
                                </div>
                            </div>
                            <div className="bg-purple-50 rounded-lg p-4">
                                <h4 className="font-medium text-purple-900 mb-2">Role-Based Access</h4>
                                <div className="space-y-1 text-sm">
                                    <p>Active Roles: {systemInfo.totalRoles || 0}</p>
                                    <p>Role Permissions: {systemInfo.roleBasedPermissions || 0}</p>
                                    <p>System Status: {systemInfo.roleBasedPermissions > 0 ? '✅ Working' : '❌ Issue'}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Navigation Tests */}
                    <div className="mb-8">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">
                            Navigation Links Test
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {testLinks.map((link) => {
                                const canAccess = hasPermission(link.permission);
                                
                                return (
                                    <div
                                        key={link.name}
                                        className={`border rounded-lg p-4 ${
                                            canAccess 
                                                ? 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800' 
                                                : 'border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between mb-2">
                                            <h4 className="font-medium text-gray-900">
                                                {link.name}
                                            </h4>
                                            {canAccess ? (
                                                <span className="text-green-500 text-lg">✅</span>
                                            ) : (
                                                <span className="text-red-500 text-lg">❌</span>
                                            )}
                                        </div>
                                        
                                        <p className="text-xs text-gray-600 mb-3">
                                            Path: {link.href}
                                        </p>
                                        
                                        {link.permission && (
                                            <p className="text-xs text-gray-600 mb-3">
                                                Permission: {link.permission}
                                            </p>
                                        )}
                                        
                                        {canAccess ? (
                                            <Link
                                                href={link.href}
                                                className="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            >
                                                Test Link
                                            </Link>
                                        ) : (
                                            <div className="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-red-800 bg-red-100">
                                                <span className="mr-1">⚠️</span>
                                                No Permission
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Permissions List */}
                    <div className="bg-gray-50 rounded-lg p-4">
                        <h3 className="text-lg font-medium text-gray-900 mb-3">
                            All Available Permissions
                        </h3>
                        {permissions.length > 0 ? (
                            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                {permissions.map((permission) => (
                                    <span
                                        key={permission}
                                        className="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        {permission}
                                    </span>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-gray-500">
                                No permissions assigned to user
                            </p>
                        )}
                    </div>

                    {/* Back to Dashboard */}
                    <div className="mt-8 text-center">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Back to Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
