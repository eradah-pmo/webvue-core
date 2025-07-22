import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    DocumentArrowDownIcon,
    EyeIcon,
    ExclamationTriangleIcon,
    ShieldCheckIcon,
    ClockIcon,
    UserIcon,
} from '@heroicons/react/24/outline';

export default function Index({ auditLogs, filters, filterOptions }) {
    const { t } = useTranslation();
    
    // Define route helper function if not available globally
    const route = (name, params) => {
        if (window.route) {
            return window.route(name, params);
        }
        const routes = {
            'audit-logs.index': '/audit-logs',
            'audit-logs.show': '/audit-logs',
            'audit-logs.dashboard': '/audit-logs/dashboard',
            'audit-logs.export': '/audit-logs/export'
        };
        return routes[name] || '/audit-logs';
    };
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);
    const [selectedFilters, setSelectedFilters] = useState({
        event: filters.event || '',
        module: filters.module || '',
        severity: filters.severity || '',
        user_id: filters.user_id || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('audit-logs.index'), {
            ...selectedFilters,
            search: searchTerm,
        });
    };

    const handleFilterChange = (key, value) => {
        setSelectedFilters(prev => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        router.get(route('audit-logs.index'), {
            ...selectedFilters,
            search: searchTerm,
        });
    };

    const clearFilters = () => {
        setSelectedFilters({
            event: '',
            module: '',
            severity: '',
            user_id: '',
            date_from: '',
            date_to: '',
        });
        setSearchTerm('');
        router.get(route('audit-logs.index'));
    };

    const exportLogs = () => {
        router.post(route('audit-logs.export'), {
            ...selectedFilters,
            search: searchTerm,
        });
    };

    const getSeverityBadge = (severity) => {
        const classes = {
            info: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            critical: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };

        return (
            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${classes[severity] || classes.info}`}>
                {severity === 'critical' && <ExclamationTriangleIcon className="w-3 h-3 mr-1" />}
                {severity === 'warning' && <ExclamationTriangleIcon className="w-3 h-3 mr-1" />}
                {severity === 'info' && <ShieldCheckIcon className="w-3 h-3 mr-1" />}
                {t(`audit.severity.${severity}`)}
            </span>
        );
    };

    const getEventIcon = (event) => {
        const iconClasses = "w-5 h-5";
        
        switch (event) {
            case 'login':
            case 'logout':
                return <UserIcon className={iconClasses} />;
            case 'security':
                return <ShieldCheckIcon className={iconClasses} />;
            default:
                return <ClockIcon className={iconClasses} />;
        }
    };

    return (
        <DashboardLayout>
            <Head title={t('audit.title')} />

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="md:flex md:items-center md:justify-between mb-6">
                        <div className="min-w-0 flex-1">
                            <h2 className="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                                {t('audit.title')}
                            </h2>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {t('audit.subtitle')}
                            </p>
                        </div>
                        <div className="mt-4 flex md:ml-4 md:mt-0 space-x-3">
                            <Link
                                href={route('audit-logs.dashboard')}
                                className="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                <ShieldCheckIcon className="w-4 h-4 mr-2" />
                                {t('audit.dashboard')}
                            </Link>
                            <button
                                onClick={exportLogs}
                                className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                            >
                                <DocumentArrowDownIcon className="w-4 h-4 mr-2" />
                                {t('common.export')}
                            </button>
                        </div>
                    </div>

                    {/* Search and Filters */}
                    <div className="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                        <div className="p-6">
                            <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-4">
                                <div className="flex-1">
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <MagnifyingGlassIcon className="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            type="text"
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            placeholder={t('audit.search_placeholder')}
                                            className="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                        />
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setShowFilters(!showFilters)}
                                        className="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                    >
                                        <FunnelIcon className="w-4 h-4 mr-2" />
                                        {t('common.filters')}
                                    </button>
                                    <button
                                        type="submit"
                                        className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                                    >
                                        {t('common.search')}
                                    </button>
                                </div>
                            </form>

                            {/* Advanced Filters */}
                            {showFilters && (
                                <div className="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {t('audit.filters.event')}
                                            </label>
                                            <select
                                                value={selectedFilters.event}
                                                onChange={(e) => handleFilterChange('event', e.target.value)}
                                                className="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            >
                                                <option value="">{t('common.all')}</option>
                                                {filterOptions.events.map(event => (
                                                    <option key={event} value={event}>
                                                        {t(`audit.events.${event}`, event)}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {t('audit.filters.module')}
                                            </label>
                                            <select
                                                value={selectedFilters.module}
                                                onChange={(e) => handleFilterChange('module', e.target.value)}
                                                className="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            >
                                                <option value="">{t('common.all')}</option>
                                                {filterOptions.modules.map(module => (
                                                    <option key={module} value={module}>
                                                        {t(`modules.${module}`, module)}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {t('audit.filters.severity')}
                                            </label>
                                            <select
                                                value={selectedFilters.severity}
                                                onChange={(e) => handleFilterChange('severity', e.target.value)}
                                                className="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            >
                                                <option value="">{t('common.all')}</option>
                                                {filterOptions.severities.map(severity => (
                                                    <option key={severity} value={severity}>
                                                        {t(`audit.severity.${severity}`)}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {t('audit.filters.user')}
                                            </label>
                                            <select
                                                value={selectedFilters.user_id}
                                                onChange={(e) => handleFilterChange('user_id', e.target.value)}
                                                className="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            >
                                                <option value="">{t('common.all')}</option>
                                                {filterOptions.users.map(user => (
                                                    <option key={user.id} value={user.id}>
                                                        {user.name} ({user.email})
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {t('audit.filters.date_from')}
                                            </label>
                                            <input
                                                type="date"
                                                value={selectedFilters.date_from}
                                                onChange={(e) => handleFilterChange('date_from', e.target.value)}
                                                className="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {t('audit.filters.date_to')}
                                            </label>
                                            <input
                                                type="date"
                                                value={selectedFilters.date_to}
                                                onChange={(e) => handleFilterChange('date_to', e.target.value)}
                                                className="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            />
                                        </div>
                                    </div>

                                    <div className="mt-4 flex justify-end space-x-3">
                                        <button
                                            type="button"
                                            onClick={clearFilters}
                                            className="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                        >
                                            {t('common.clear')}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={applyFilters}
                                            className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                        >
                                            {t('common.apply')}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Audit Logs Table */}
                    <div className="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                        <ul className="divide-y divide-gray-200 dark:divide-gray-700">
                            {auditLogs.data.map((log) => (
                                <li key={log.id}>
                                    <div className="px-4 py-4 flex items-center justify-between">
                                        <div className="flex items-center min-w-0 flex-1">
                                            <div className="flex-shrink-0">
                                                <div className="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                                    {getEventIcon(log.event)}
                                                </div>
                                            </div>
                                            <div className="ml-4 min-w-0 flex-1">
                                                <div className="flex items-center">
                                                    <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        {log.description || t(`audit.events.${log.event}`, log.event)}
                                                    </p>
                                                    <div className="ml-2">
                                                        {getSeverityBadge(log.severity)}
                                                    </div>
                                                </div>
                                                <div className="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                    <span>{log.user?.name || t('common.system')}</span>
                                                    <span className="mx-2">•</span>
                                                    <span>{log.module}</span>
                                                    <span className="mx-2">•</span>
                                                    <span>{new Date(log.created_at).toLocaleString()}</span>
                                                    {log.ip_address && (
                                                        <>
                                                            <span className="mx-2">•</span>
                                                            <span>{log.ip_address}</span>
                                                        </>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="ml-4 flex-shrink-0">
                                            <Link
                                                href={route('audit-logs.show', log.id)}
                                                className="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                            >
                                                <EyeIcon className="w-4 h-4 mr-1" />
                                                {t('common.view')}
                                            </Link>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>

                        {/* Pagination */}
                        {auditLogs.links && (
                            <div className="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex-1 flex justify-between sm:hidden">
                                        {auditLogs.prev_page_url && (
                                            <Link
                                                href={auditLogs.prev_page_url}
                                                className="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                            >
                                                {t('common.previous')}
                                            </Link>
                                        )}
                                        {auditLogs.next_page_url && (
                                            <Link
                                                href={auditLogs.next_page_url}
                                                className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                            >
                                                {t('common.next')}
                                            </Link>
                                        )}
                                    </div>
                                    <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm text-gray-700 dark:text-gray-300">
                                                {t('common.showing')} <span className="font-medium">{auditLogs.from}</span> {t('common.to')} <span className="font-medium">{auditLogs.to}</span> {t('common.of')} <span className="font-medium">{auditLogs.total}</span> {t('common.results')}
                                            </p>
                                        </div>
                                        <div>
                                            <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                {auditLogs.links.map((link, index) => (
                                                    <Link
                                                        key={index}
                                                        href={link.url || '#'}
                                                        className={`relative inline-flex items-center px-2 py-2 border text-sm font-medium ${
                                                            link.active
                                                                ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600'
                                                        } ${index === 0 ? 'rounded-l-md' : ''} ${index === auditLogs.links.length - 1 ? 'rounded-r-md' : ''}`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                    />
                                                ))}
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
