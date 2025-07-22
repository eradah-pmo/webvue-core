import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { useTranslation } from 'react-i18next';
import {
    MagnifyingGlassIcon,
    PlusIcon,
    FunnelIcon,
    ArrowPathIcon,
    Cog6ToothIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    DocumentArrowDownIcon,
} from '@heroicons/react/24/outline';
import { toast } from 'react-hot-toast';

export default function Index({ settings, categories, filters = {} }) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedCategory, setSelectedCategory] = useState(filters.category || '');
    const [isLoading, setIsLoading] = useState(false);
    const [expandedCategories, setExpandedCategories] = useState(new Set(['general']));

    // Group settings by category
    const groupedSettings = settings.data.reduce((groups, setting) => {
        const category = setting.category || 'general';
        if (!groups[category]) {
            groups[category] = [];
        }
        groups[category].push(setting);
        return groups;
    }, {});

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('settings.index'), {
            search: searchTerm,
            category: selectedCategory,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleCategoryFilter = (category) => {
        setSelectedCategory(category);
        router.get(route('settings.index'), {
            search: searchTerm,
            category: category,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleDelete = async (settingId, settingKey) => {
        if (!confirm(t('settings.confirm_delete', { key: settingKey }))) {
            return;
        }

        setIsLoading(true);
        try {
            await router.delete(route('settings.destroy', settingId), {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success(t('settings.deleted_successfully'));
                },
                onError: () => {
                    toast.error(t('settings.deletion_failed'));
                },
            });
        } catch (error) {
            toast.error(t('settings.deletion_failed'));
        } finally {
            setIsLoading(false);
        }
    };

    const handleClearCache = async () => {
        setIsLoading(true);
        try {
            await router.post(route('settings.clear-cache'), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success(t('settings.cache_cleared'));
                },
                onError: () => {
                    toast.error(t('settings.cache_clear_failed'));
                },
            });
        } catch (error) {
            toast.error(t('settings.cache_clear_failed'));
        } finally {
            setIsLoading(false);
        }
    };

    const toggleCategory = (category) => {
        const newExpanded = new Set(expandedCategories);
        if (newExpanded.has(category)) {
            newExpanded.delete(category);
        } else {
            newExpanded.add(category);
        }
        setExpandedCategories(newExpanded);
    };

    const getTypeIcon = (type) => {
        switch (type) {
            case 'file':
                return <DocumentArrowDownIcon className="h-4 w-4" />;
            case 'boolean':
                return <div className="h-4 w-4 rounded border-2 border-gray-400" />;
            case 'number':
                return <span className="text-xs font-mono">#</span>;
            case 'json':
                return <span className="text-xs font-mono">{}</span>;
            default:
                return <span className="text-xs font-mono">Aa</span>;
        }
    };

    const formatValue = (setting) => {
        if (setting.is_encrypted) {
            return '••••••••';
        }
        
        switch (setting.type) {
            case 'boolean':
                return setting.value ? t('common.yes') : t('common.no');
            case 'file':
                return setting.value ? (
                    <a 
                        href={`/storage/${setting.value}`} 
                        target="_blank" 
                        className="text-blue-600 hover:text-blue-800 underline"
                    >
                        {t('settings.view_file')}
                    </a>
                ) : t('common.no_file');
            case 'json':
                return (
                    <code className="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                        {JSON.stringify(setting.value)}
                    </code>
                );
            default:
                return setting.value || t('common.empty');
        }
    };

    return (
        <DashboardLayout>
            <Head title={t('settings.title')} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div className="flex items-center space-x-3">
                        <Cog6ToothIcon className="h-8 w-8 text-gray-600 dark:text-gray-400" />
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                                {t('settings.system_settings')}
                            </h1>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('settings.manage_settings_description')}
                            </p>
                        </div>
                    </div>
                    
                    <div className="flex items-center space-x-3">
                        <button
                            onClick={handleClearCache}
                            disabled={isLoading}
                            className="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <ArrowPathIcon className={`h-4 w-4 mr-2 ${isLoading ? 'animate-spin' : ''}`} />
                            {t('settings.clear_cache')}
                        </button>
                        
                        <Link
                            href={route('settings.create')}
                            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <PlusIcon className="h-4 w-4 mr-2" />
                            {t('settings.create_setting')}
                        </Link>
                    </div>
                </div>

                {/* Search and Filters */}
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-4">
                        <div className="flex-1">
                            <div className="relative">
                                <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input
                                    type="text"
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    placeholder={t('settings.search_settings')}
                                    className="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
                        
                        <div className="sm:w-48">
                            <select
                                value={selectedCategory}
                                onChange={(e) => handleCategoryFilter(e.target.value)}
                                className="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">{t('settings.all_categories')}</option>
                                {categories.map((category) => (
                                    <option key={category} value={category}>
                                        {t(`settings.${category}`)}
                                    </option>
                                ))}
                            </select>
                        </div>
                        
                        <button
                            type="submit"
                            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <FunnelIcon className="h-4 w-4 mr-2" />
                            {t('common.filter')}
                        </button>
                    </form>
                </div>

                {/* Settings by Category */}
                <div className="space-y-6">
                    {Object.entries(groupedSettings).map(([category, categorySettings]) => (
                        <div key={category} className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div 
                                className="px-6 py-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                onClick={() => toggleCategory(category)}
                            >
                                <div className="flex items-center justify-between">
                                    <h3 className="text-lg font-medium text-gray-900 dark:text-white capitalize">
                                        {t(`settings.${category}`)} 
                                        <span className="ml-2 text-sm text-gray-500">({categorySettings.length})</span>
                                    </h3>
                                    <div className={`transform transition-transform ${expandedCategories.has(category) ? 'rotate-180' : ''}`}>
                                        <svg className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            {expandedCategories.has(category) && (
                                <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {categorySettings.map((setting) => (
                                        <div key={setting.id} className="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <div className="flex items-center justify-between">
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center space-x-3">
                                                        <div className="flex-shrink-0">
                                                            {getTypeIcon(setting.type)}
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <div className="flex items-center space-x-2">
                                                                <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                                    {setting.key}
                                                                </p>
                                                                {setting.is_public && (
                                                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                                        {t('settings.public')}
                                                                    </span>
                                                                )}
                                                                {setting.is_encrypted && (
                                                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                                        {t('settings.encrypted')}
                                                                    </span>
                                                                )}
                                                            </div>
                                                            <p className="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                                {setting.description || t('common.no_description')}
                                                            </p>
                                                            <div className="mt-1">
                                                                <span className="text-sm text-gray-900 dark:text-white">
                                                                    {formatValue(setting)}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div className="flex items-center space-x-2">
                                                    <Link
                                                        href={route('settings.edit', setting.id)}
                                                        className="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                        title={t('settings.edit_setting')}
                                                    >
                                                        <PencilIcon className="h-4 w-4" />
                                                    </Link>
                                                    
                                                    <button
                                                        onClick={() => handleDelete(setting.id, setting.key)}
                                                        disabled={isLoading}
                                                        className="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50"
                                                        title={t('settings.delete_setting')}
                                                    >
                                                        <TrashIcon className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    ))}
                </div>

                {/* Pagination */}
                {settings.links && (
                    <div className="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6 rounded-lg">
                        <div className="flex items-center justify-between">
                            <div className="flex-1 flex justify-between sm:hidden">
                                {settings.prev_page_url && (
                                    <Link
                                        href={settings.prev_page_url}
                                        className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        {t('common.previous')}
                                    </Link>
                                )}
                                {settings.next_page_url && (
                                    <Link
                                        href={settings.next_page_url}
                                        className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        {t('common.next')}
                                    </Link>
                                )}
                            </div>
                            <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p className="text-sm text-gray-700 dark:text-gray-300">
                                        {t('common.showing')} <span className="font-medium">{settings.from}</span> {t('common.to')} <span className="font-medium">{settings.to}</span> {t('common.of')} <span className="font-medium">{settings.total}</span> {t('common.results')}
                                    </p>
                                </div>
                                <div>
                                    <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        {settings.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url || '#'}
                                                className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                                                    link.active
                                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                } ${index === 0 ? 'rounded-l-md' : ''} ${index === settings.links.length - 1 ? 'rounded-r-md' : ''}`}
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
        </DashboardLayout>
    );
}