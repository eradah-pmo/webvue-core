import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon,
    Cog6ToothIcon,
    KeyIcon,
    DocumentTextIcon,
    PhotoIcon,
    ToggleIcon,
    NumberedListIcon
} from '@heroicons/react/24/outline';

export default function SettingsIndex({ settings, categories, filters }) {
    const { t } = useTranslation(['settings', 'common']);
    const [processing, setProcessing] = useState(false);
    const [selectedCategory, setSelectedCategory] = useState(filters?.category || 'all');

    const handleDelete = (setting) => {
        if (confirm(t('common:confirmDelete'))) {
            setProcessing(true);
            router.delete(route('settings.destroy', setting.id), {
                onFinish: () => setProcessing(false)
            });
        }
    };

    const getTypeIcon = (type) => {
        const icons = {
            'text': DocumentTextIcon,
            'number': NumberedListIcon,
            'boolean': ToggleIcon,
            'file': PhotoIcon,
            'password': KeyIcon,
        };
        return icons[type] || DocumentTextIcon;
    };

    const getTypeColor = (type) => {
        const colors = {
            'text': 'text-blue-600 bg-blue-100',
            'number': 'text-green-600 bg-green-100',
            'boolean': 'text-purple-600 bg-purple-100',
            'file': 'text-orange-600 bg-orange-100',
            'password': 'text-red-600 bg-red-100',
        };
        return colors[type] || 'text-gray-600 bg-gray-100';
    };

    const filteredSettings = selectedCategory === 'all' 
        ? settings 
        : settings.filter(setting => setting.category === selectedCategory);

    return (
        <DashboardLayout>
            <Head title={t('settings:title')} />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="md:flex md:items-center md:justify-between mb-6">
                        <div className="flex-1 min-w-0">
                            <h2 className="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
                                {t('settings:title')}
                            </h2>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {t('settings:description')}
                            </p>
                        </div>
                        <div className="mt-4 flex md:mt-0 md:ml-4">
                            <Link
                                href={route('settings.create')}
                                className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                {t('settings:add')}
                            </Link>
                        </div>
                    </div>

                    {/* Categories Filter */}
                    <div className="mb-6">
                        <div className="border-b border-gray-200 dark:border-gray-700">
                            <nav className="-mb-px flex space-x-8">
                                <button
                                    onClick={() => setSelectedCategory('all')}
                                    className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                        selectedCategory === 'all'
                                            ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                    }`}
                                >
                                    {t('settings:all_categories')}
                                </button>
                                {categories.map((category) => (
                                    <button
                                        key={category}
                                        onClick={() => setSelectedCategory(category)}
                                        className={`py-2 px-1 border-b-2 font-medium text-sm capitalize ${
                                            selectedCategory === category
                                                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                        }`}
                                    >
                                        {t(`settings:categories.${category}`)}
                                    </button>
                                ))}
                            </nav>
                        </div>
                    </div>

                    {/* Settings Grid */}
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {filteredSettings.map((setting) => {
                            const TypeIcon = getTypeIcon(setting.type);
                            return (
                                <div
                                    key={setting.id}
                                    className="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200"
                                >
                                    <div className="p-6">
                                        <div className="flex items-center justify-between mb-4">
                                            <div className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTypeColor(setting.type)}`}>
                                                <TypeIcon className="w-3 h-3 mr-1" />
                                                {setting.type}
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Link
                                                    href={route('settings.edit', setting.id)}
                                                    className="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400"
                                                    title={t('common:edit')}
                                                >
                                                    <PencilIcon className="h-4 w-4" />
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(setting)}
                                                    className="text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                                    title={t('common:delete')}
                                                    disabled={processing}
                                                >
                                                    <TrashIcon className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div className="mb-3">
                                            <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                                {setting.display_name || setting.key}
                                            </h3>
                                            <p className="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                                {setting.key}
                                            </p>
                                        </div>

                                        {setting.description && (
                                            <p className="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                                {setting.description}
                                            </p>
                                        )}

                                        <div className="border-t border-gray-200 dark:border-gray-700 pt-3">
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="text-gray-500 dark:text-gray-400">
                                                    {t('settings:category')}:
                                                </span>
                                                <span className="text-gray-900 dark:text-white capitalize">
                                                    {t(`settings:categories.${setting.category}`)}
                                                </span>
                                            </div>
                                            {setting.type !== 'password' && (
                                                <div className="flex items-center justify-between text-sm mt-2">
                                                    <span className="text-gray-500 dark:text-gray-400">
                                                        {t('settings:current_value')}:
                                                    </span>
                                                    <span className="text-gray-900 dark:text-white truncate max-w-32" title={setting.value}>
                                                        {setting.type === 'boolean' 
                                                            ? (setting.value ? t('common:yes') : t('common:no'))
                                                            : (setting.value || t('common:not_set'))
                                                        }
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Empty State */}
                    {filteredSettings.length === 0 && (
                        <div className="text-center py-12">
                            <Cog6ToothIcon className="mx-auto h-12 w-12 text-gray-400" />
                            <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                {t('settings:no_settings')}
                            </h3>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {t('settings:no_settings_description')}
                            </p>
                            <div className="mt-6">
                                <Link
                                    href={route('settings.create')}
                                    className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                >
                                    <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                    {t('settings:add_first')}
                                </Link>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </DashboardLayout>
    );
}
