import React from 'react';
import { Link, usePage } from '@inertiajs/react';
// import { useTranslation } from 'react-i18next'; // Temporarily disabled
import {
    HomeIcon,
    UsersIcon,
    ShieldCheckIcon,
    BuildingOfficeIcon,
    CubeIcon,
    Cog6ToothIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/react/24/outline';
import clsx from 'clsx';

// Simple icon validation helper
const isValidReactComponent = (component) => {
    return component && (typeof component === 'function' || (component.$$typeof && component.render));
};

// Verify all icons are properly imported (only log errors)
const verifyIcons = () => {
    const icons = {
        HomeIcon,
        UsersIcon,
        ShieldCheckIcon,
        BuildingOfficeIcon,
        CubeIcon,
        Cog6ToothIcon,
        ChevronLeftIcon,
        ChevronRightIcon,
    };
    
    const invalidIcons = Object.entries(icons).filter(([name, icon]) => !isValidReactComponent(icon));
    
    if (invalidIcons.length > 0) {
        console.error('❌ Invalid icons detected:', invalidIcons.map(([name]) => name));
    }
};

// Run verification in development
if (process.env.NODE_ENV === 'development') {
    verifyIcons();
}

// Navigation labels in English (temporarily disabled i18n)
const getNavigationLabel = (name) => {
    const labels = {
        'dashboard': 'Dashboard',
        'test_navigation': 'Navigation Test',
        'users': 'Users',
        'roles': 'Roles & Permissions',
        'departments': 'Departments',
        'modules': 'Modules',
        'settings': 'Settings',
        'audit_logs': 'Audit Logs'
    };
    return labels[name] || name.charAt(0).toUpperCase() + name.slice(1);
};

const defaultNavigation = [
    { name: 'dashboard', href: '/dashboard', icon: HomeIcon }, // Dashboard doesn't need permission
    { name: 'test_navigation', href: '/test-navigation', icon: CubeIcon }, // Diagnostic test - no permission needed
    { name: 'users', href: '/users', icon: UsersIcon, permission: 'users.view' },
    { name: 'roles', href: '/roles', icon: ShieldCheckIcon, permission: 'roles.view' },
    { name: 'departments', href: '/departments', icon: BuildingOfficeIcon, permission: 'departments.view' },
    { name: 'settings', href: '/settings', icon: Cog6ToothIcon, permission: 'settings.view' },
];

export default function Sidebar({ open, collapsed, onClose, modules = [], user }) {
    // const { t } = useTranslation(); // Temporarily disabled - using English only
    const { url, props } = usePage();
    
    // Get user data from Inertia props if not passed directly
    const currentUser = user || props.auth?.user;
    
    // Get navigation from server (preferred) or use default as fallback
    const serverNavigation = props.navigation || [];
    const navigation = serverNavigation.length > 0 ? serverNavigation : defaultNavigation;
    
    // Log basic info for troubleshooting if needed
    if (process.env.NODE_ENV === 'development' && !currentUser?.roles?.length) {
        console.log('🔍 Sidebar: No user roles found, check authentication');
    }
    
    // Simplified permission filtering - if server provides navigation, it's already filtered
    const filteredNavigation = serverNavigation.length > 0 
        ? serverNavigation // Server already filtered based on user permissions
        : navigation.filter(item => {
            // Fallback filtering for default navigation
            
            // If no user data, show dashboard only
            if (!currentUser) {
                return item.name === 'dashboard';
            }
            
            // Super admin and admin get everything
            if (currentUser?.roles?.includes('super-admin') || currentUser?.roles?.includes('admin')) {
                return true;
            }
            
            // If no permission required, allow
            if (!item.permission) {
                return true;
            }
            
            // Check specific permission
            return currentUser?.permissions?.includes(item.permission);
        });
    
    // Navigation filtering completed

    const isCurrentPage = (href) => {
        return url === href || url.startsWith(href + '/');
    };

    return (
        <>
            {/* Desktop sidebar */}
            <div className={clsx(
                'hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:z-50 transition-all duration-300',
                collapsed ? 'lg:w-16' : 'lg:w-64'
            )}>
                <div className="flex flex-col flex-grow bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-sm">
                    {/* Logo */}
                    <div className={clsx(
                        'flex items-center flex-shrink-0 px-4 py-4 border-b border-gray-200 dark:border-gray-700',
                        collapsed ? 'justify-center' : 'justify-between'
                    )}>
                        <div className="flex items-center">
                            <div className="flex-shrink-0 w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                                <span className="text-white font-bold text-sm">MA</span>
                            </div>
                            {!collapsed && (
                                <div className="ml-3">
                                    <h1 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        Admin Dashboard
                                    </h1>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Navigation */}
                    <nav className="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                        {filteredNavigation.length > 0 ? filteredNavigation.map((item) => {
                            // Handle both server navigation format and default navigation format
                            const itemName = item.name || item.key || 'unknown';
                            const itemHref = item.href || item.url || '#';
                            const itemTitle = item.title || item.display_name || itemName;
                            
                            // Get icon - server navigation might use different format
                            let Icon = item.icon;
                            if (!Icon && itemName) {
                                // Fallback to default icons based on name
                                const iconMap = {
                                    'dashboard': HomeIcon,
                                    'users': UsersIcon,
                                    'roles': ShieldCheckIcon,
                                    'departments': BuildingOfficeIcon,
                                    'settings': Cog6ToothIcon,
                                    'modules': CubeIcon
                                };
                                Icon = iconMap[itemName] || HomeIcon;
                            }
                            
                            // Safety check: ensure Icon is a valid React component
                            if (!isValidReactComponent(Icon)) {
                                console.warn(`⚠️ Using fallback icon for ${itemName}`);
                                Icon = HomeIcon;
                            }
                            
                            const current = isCurrentPage(itemHref);
                            
                            // Final safety check before rendering
                            if (!Icon) {
                                console.error(`❌ No icon available for ${itemName}, skipping render`);
                                return null;
                            }
                            
                            return (
                                <Link
                                    key={itemName}
                                    href={itemHref}
                                    className={clsx(
                                        'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-150',
                                        current
                                            ? 'bg-primary-100 text-primary-900 dark:bg-primary-900 dark:text-primary-100'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white',
                                        collapsed ? 'justify-center' : ''
                                    )}
                                    title={collapsed ? (itemTitle || getNavigationLabel(itemName)) : ''}
                                    preserveScroll={false}
                                    preserveState={false}
                                >
                                    <Icon
                                        className={clsx(
                                            'flex-shrink-0 w-5 h-5',
                                            current ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400',
                                            collapsed ? '' : 'mr-3'
                                        )}
                                    />
                                    {!collapsed && (
                                        <span className="truncate">
                                            {itemTitle || getNavigationLabel(itemName)}
                                        </span>
                                    )}
                                </Link>
                            );
                        }) : (
                            <div className="px-2 py-4 text-center text-gray-500 dark:text-gray-400">
                                <p className="text-sm">No navigation items available</p>
                                <p className="text-xs mt-1">Please check your permissions</p>
                            </div>
                        )}
                    </nav>

                    {/* User info */}
                    {!collapsed && (
                        <div className="flex-shrink-0 p-4 border-t border-gray-200 dark:border-gray-700">
                            <div className="flex items-center">
                                <div className="flex-shrink-0 w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                    <span className="text-sm font-medium text-gray-700">
                                        {user.initials || user.name?.charAt(0) || 'U'}
                                    </span>
                                </div>
                                <div className="ml-3 flex-1 min-w-0">
                                    <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {user.full_name || user.name}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {user.email}
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Mobile sidebar */}
            <div className={clsx(
                'lg:hidden fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 ease-in-out',
                open ? 'translate-x-0' : '-translate-x-full'
            )}>
                <div className="flex flex-col h-full bg-white dark:bg-gray-800 shadow-xl">
                    {/* Mobile header */}
                    <div className="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div className="flex items-center">
                            <div className="flex-shrink-0 w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                                <span className="text-white font-bold text-sm">MA</span>
                            </div>
                            <div className="ml-3">
                                <h1 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    Modular Admin Dashboard
                                </h1>
                            </div>
                        </div>
                        <button
                            onClick={onClose}
                            className="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <ChevronLeftIcon className="w-5 h-5" />
                        </button>
                    </div>

                    {/* Mobile navigation */}
                    <nav className="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                        {filteredNavigation.map((item) => {
                            let Icon = item.icon;
                            
                            // Apply same icon validation logic as desktop navigation
                            if (!Icon || !isValidReactComponent(Icon)) {
                                const itemName = item.name?.toLowerCase();
                                const iconMap = {
                                    'dashboard': HomeIcon,
                                    'users': UsersIcon,
                                    'roles': ShieldCheckIcon,
                                    'departments': BuildingOfficeIcon,
                                    'settings': Cog6ToothIcon,
                                    'modules': CubeIcon
                                };
                                Icon = iconMap[itemName] || HomeIcon;
                            }
                            
                            // Safety check: ensure Icon is a valid React component
                            if (!isValidReactComponent(Icon)) {
                                console.warn(`⚠️ Using fallback icon for mobile ${item.name}`);
                                Icon = HomeIcon;
                            }
                            
                            const current = isCurrentPage(item.href);
                            
                            // Final safety check before rendering
                            if (!Icon) {
                                console.error(`❌ No icon available for mobile ${item.name}, skipping render`);
                                return null;
                            }
                            
                            return (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    onClick={onClose}
                                    className={clsx(
                                        'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-150',
                                        current
                                            ? 'bg-primary-100 text-primary-900 dark:bg-primary-900 dark:text-primary-100'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'
                                    )}
                                    preserveScroll={false}
                                    preserveState={false}
                                >
                                    <Icon
                                        className={clsx(
                                            'mr-3 flex-shrink-0 w-5 h-5',
                                            current ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-500'
                                        )}
                                    />
                                    <span className="truncate">
                                        {getNavigationLabel(item.name)}
                                    </span>
                                </Link>
                            );
                        })}
                    </nav>

                    {/* Mobile user info */}
                    <div className="flex-shrink-0 p-4 border-t border-gray-200 dark:border-gray-700">
                        <div className="flex items-center">
                            <div className="flex-shrink-0 w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <span className="text-sm font-medium text-gray-700">
                                    {user.initials || user.name?.charAt(0) || 'U'}
                                </span>
                            </div>
                            <div className="ml-3 flex-1 min-w-0">
                                <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {user.full_name || user.name}
                                </p>
                                <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {user.email}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
