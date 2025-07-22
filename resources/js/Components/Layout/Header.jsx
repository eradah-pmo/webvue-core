import React, { useState, useRef, useEffect } from 'react';
import { Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import {
    Bars3Icon,
    ChevronLeftIcon,
    ChevronRightIcon,
    BellIcon,
    UserCircleIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    LanguageIcon,
    SunIcon,
    MoonIcon,
} from '@heroicons/react/24/outline';
import clsx from 'clsx';

export default function Header({ user, onMenuClick, onSidebarToggle, sidebarCollapsed }) {
    const { t, i18n } = useTranslation();
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const [languageMenuOpen, setLanguageMenuOpen] = useState(false);
    const [darkMode, setDarkMode] = useState(false);
    const userMenuRef = useRef(null);
    const languageMenuRef = useRef(null);

    // Close dropdowns when clicking outside
    useEffect(() => {
        function handleClickOutside(event) {
            if (userMenuRef.current && !userMenuRef.current.contains(event.target)) {
                setUserMenuOpen(false);
            }
            if (languageMenuRef.current && !languageMenuRef.current.contains(event.target)) {
                setLanguageMenuOpen(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleLanguageChange = (lng) => {
        i18n.changeLanguage(lng);
        setLanguageMenuOpen(false);
        
        // Save language preference to backend
        router.post('/user/language', { language: lng }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleLogout = () => {
        router.post('/logout');
    };

    const toggleDarkMode = () => {
        setDarkMode(!darkMode);
        document.documentElement.classList.toggle('dark');
    };

    return (
        <header className="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div className="flex items-center justify-between px-4 py-3 lg:px-6">
                {/* Left side */}
                <div className="flex items-center space-x-4">
                    {/* Mobile menu button */}
                    <button
                        onClick={onMenuClick}
                        className="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <Bars3Icon className="w-6 h-6" />
                    </button>

                    {/* Desktop sidebar toggle */}
                    <button
                        onClick={onSidebarToggle}
                        className="hidden lg:flex p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        {sidebarCollapsed ? (
                            <ChevronRightIcon className="w-5 h-5" />
                        ) : (
                            <ChevronLeftIcon className="w-5 h-5" />
                        )}
                    </button>

                    {/* Breadcrumb or page title could go here */}
                    <div className="hidden sm:block">
                        <h1 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {/* This could be dynamic based on current page */}
                        </h1>
                    </div>
                </div>

                {/* Right side */}
                <div className="flex items-center space-x-2">
                    {/* Dark mode toggle */}
                    <button
                        onClick={toggleDarkMode}
                        className="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        title={darkMode ? t('common:actions.disable') + ' Dark Mode' : t('common:actions.enable') + ' Dark Mode'}
                    >
                        {darkMode ? (
                            <SunIcon className="w-5 h-5" />
                        ) : (
                            <MoonIcon className="w-5 h-5" />
                        )}
                    </button>

                    {/* Language selector */}
                    <div className="relative" ref={languageMenuRef}>
                        <button
                            onClick={() => setLanguageMenuOpen(!languageMenuOpen)}
                            className="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            title={t('common:language.switch_language')}
                        >
                            <LanguageIcon className="w-5 h-5" />
                        </button>

                        {languageMenuOpen && (
                            <div className="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                <div className="py-1">
                                    <button
                                        onClick={() => handleLanguageChange('en')}
                                        className={clsx(
                                            'block w-full text-left px-4 py-2 text-sm transition-colors',
                                            i18n.language === 'en'
                                                ? 'bg-primary-100 text-primary-900 dark:bg-primary-900 dark:text-primary-100'
                                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                        )}
                                    >
                                        {t('common:language.english')}
                                    </button>
                                    <button
                                        onClick={() => handleLanguageChange('ar')}
                                        className={clsx(
                                            'block w-full text-left px-4 py-2 text-sm transition-colors',
                                            i18n.language === 'ar'
                                                ? 'bg-primary-100 text-primary-900 dark:bg-primary-900 dark:text-primary-100'
                                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                        )}
                                    >
                                        {t('common:language.arabic')}
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Notifications */}
                    <button className="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors relative">
                        <BellIcon className="w-5 h-5" />
                        {/* Notification badge */}
                        <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    {/* User menu */}
                    <div className="relative" ref={userMenuRef}>
                        <button
                            onClick={() => setUserMenuOpen(!userMenuOpen)}
                            className="flex items-center space-x-2 p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            {user.avatar ? (
                                <img
                                    src={user.avatar}
                                    alt={user.full_name || user.name}
                                    className="w-6 h-6 rounded-full"
                                />
                            ) : (
                                <div className="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                    <span className="text-xs font-medium text-gray-700">
                                        {user.initials || user.name?.charAt(0) || 'U'}
                                    </span>
                                </div>
                            )}
                            <span className="hidden sm:block text-sm font-medium text-gray-900 dark:text-white">
                                {user.full_name || user.name}
                            </span>
                        </button>

                        {userMenuOpen && (
                            <div className="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                <div className="py-1">
                                    <Link
                                        href="/profile"
                                        className="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        onClick={() => setUserMenuOpen(false)}
                                    >
                                        <UserCircleIcon className="w-4 h-4 mr-3" />
                                        Profile
                                    </Link>
                                    <Link
                                        href="/settings"
                                        className="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        onClick={() => setUserMenuOpen(false)}
                                    >
                                        <Cog6ToothIcon className="w-4 h-4 mr-3" />
                                        {t('common:navigation.settings')}
                                    </Link>
                                    <hr className="my-1 border-gray-200 dark:border-gray-600" />
                                    <button
                                        onClick={handleLogout}
                                        className="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <ArrowRightOnRectangleIcon className="w-4 h-4 mr-3" />
                                        {t('common:navigation.logout')}
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </header>
    );
}
