import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { 
    ArrowLeftIcon,
    KeyIcon,
    DocumentTextIcon,
    PhotoIcon,
    ToggleIcon,
    NumberedListIcon
} from '@heroicons/react/24/outline';

export default function SettingsForm({ setting = null, categories = [] }) {
    const { t } = useTranslation(['settings', 'common']);
    const isEditing = !!setting;
    
    const { data, setData, post, put, processing, errors, reset } = useForm({
        key: setting?.key || '',
        display_name: setting?.display_name || '',
        description: setting?.description || '',
        value: setting?.value || '',
        type: setting?.type || 'text',
        category: setting?.category || (categories[0] || 'general'),
        is_public: setting?.is_public || false,
        file: null,
    });

    const [showPassword, setShowPassword] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (isEditing) {
            put(route('settings.update', setting.id), {
                onSuccess: () => {
                    // Handle success
                }
            });
        } else {
            post(route('settings.store'), {
                onSuccess: () => {
                    reset();
                }
            });
        }
    };

    const typeOptions = [
        { value: 'text', label: t('settings:types.text'), icon: DocumentTextIcon },
        { value: 'number', label: t('settings:types.number'), icon: NumberedListIcon },
        { value: 'boolean', label: t('settings:types.boolean'), icon: ToggleIcon },
        { value: 'file', label: t('settings:types.file'), icon: PhotoIcon },
        { value: 'password', label: t('settings:types.password'), icon: KeyIcon },
    ];

    const getTypeIcon = (type) => {
        const option = typeOptions.find(opt => opt.value === type);
        return option ? option.icon : DocumentTextIcon;
    };

    return (
        <DashboardLayout>
            <Head title={isEditing ? t('settings:edit') : t('settings:create')} />
            
            <div className="py-6">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-6">
                        <div className="flex items-center space-x-3 mb-4">
                            <button
                                onClick={() => window.history.back()}
                                className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                            >
                                <ArrowLeftIcon className="w-4 h-4 mr-1" />
                                {t('common:back')}
                            </button>
                        </div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                            {isEditing ? t('settings:edit') : t('settings:create')}
                        </h1>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {isEditing ? t('settings:edit_description') : t('settings:create_description')}
                        </p>
                    </div>

                    {/* Form */}
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div className="px-6 py-6 space-y-6">
                                {/* Key */}
                                <div>
                                    <label htmlFor="key" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {t('settings:key')} <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="key"
                                        value={data.key}
                                        onChange={(e) => setData('key', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        placeholder="app.name"
                                        disabled={isEditing}
                                        required
                                    />
                                    {errors.key && <p className="mt-1 text-sm text-red-600">{errors.key}</p>}
                                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {t('settings:key_help')}
                                    </p>
                                </div>

                                {/* Display Name */}
                                <div>
                                    <label htmlFor="display_name" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {t('settings:display_name')}
                                    </label>
                                    <input
                                        type="text"
                                        id="display_name"
                                        value={data.display_name}
                                        onChange={(e) => setData('display_name', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        placeholder={t('settings:display_name_placeholder')}
                                    />
                                    {errors.display_name && <p className="mt-1 text-sm text-red-600">{errors.display_name}</p>}
                                </div>

                                {/* Description */}
                                <div>
                                    <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {t('settings:description')}
                                    </label>
                                    <textarea
                                        id="description"
                                        rows={3}
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        placeholder={t('settings:description_placeholder')}
                                    />
                                    {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                                </div>

                                {/* Type */}
                                <div>
                                    <label htmlFor="type" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {t('settings:type')} <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="type"
                                        value={data.type}
                                        onChange={(e) => setData('type', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        required
                                    >
                                        {typeOptions.map((option) => {
                                            const Icon = option.icon;
                                            return (
                                                <option key={option.value} value={option.value}>
                                                    {option.label}
                                                </option>
                                            );
                                        })}
                                    </select>
                                    {errors.type && <p className="mt-1 text-sm text-red-600">{errors.type}</p>}
                                </div>

                                {/* Category */}
                                <div>
                                    <label htmlFor="category" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {t('settings:category')} <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="category"
                                        value={data.category}
                                        onChange={(e) => setData('category', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        required
                                    >
                                        {categories.map((category) => (
                                            <option key={category} value={category}>
                                                {t(`settings:categories.${category}`)}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.category && <p className="mt-1 text-sm text-red-600">{errors.category}</p>}
                                </div>

                                {/* Value */}
                                <div>
                                    <label htmlFor="value" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {t('settings:value')}
                                    </label>
                                    
                                    {data.type === 'boolean' ? (
                                        <div className="mt-1">
                                            <label className="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    checked={data.value === 'true' || data.value === true}
                                                    onChange={(e) => setData('value', e.target.checked)}
                                                    className="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                />
                                                <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    {t('settings:boolean_help')}
                                                </span>
                                            </label>
                                        </div>
                                    ) : data.type === 'file' ? (
                                        <div className="mt-1">
                                            <input
                                                type="file"
                                                id="file"
                                                onChange={(e) => setData('file', e.target.files[0])}
                                                className="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                                            />
                                            {setting?.value && (
                                                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {t('settings:current_file')}: {setting.value}
                                                </p>
                                            )}
                                        </div>
                                    ) : data.type === 'password' ? (
                                        <div className="mt-1 relative">
                                            <input
                                                type={showPassword ? 'text' : 'password'}
                                                id="value"
                                                value={data.value}
                                                onChange={(e) => setData('value', e.target.value)}
                                                className="block w-full pr-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                                placeholder={t('settings:password_placeholder')}
                                            />
                                            <button
                                                type="button"
                                                className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                                onClick={() => setShowPassword(!showPassword)}
                                            >
                                                <span className="text-gray-400 hover:text-gray-600 text-sm">
                                                    {showPassword ? t('common:hide') : t('common:show')}
                                                </span>
                                            </button>
                                        </div>
                                    ) : data.type === 'number' ? (
                                        <input
                                            type="number"
                                            id="value"
                                            value={data.value}
                                            onChange={(e) => setData('value', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                            placeholder={t('settings:number_placeholder')}
                                        />
                                    ) : (
                                        <input
                                            type="text"
                                            id="value"
                                            value={data.value}
                                            onChange={(e) => setData('value', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                            placeholder={t('settings:value_placeholder')}
                                        />
                                    )}
                                    {errors.value && <p className="mt-1 text-sm text-red-600">{errors.value}</p>}
                                </div>

                                {/* Is Public */}
                                <div>
                                    <div className="flex items-center">
                                        <input
                                            id="is_public"
                                            type="checkbox"
                                            checked={data.is_public}
                                            onChange={(e) => setData('is_public', e.target.checked)}
                                            className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                        />
                                        <label htmlFor="is_public" className="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            {t('settings:is_public')}
                                        </label>
                                    </div>
                                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {t('settings:is_public_help')}
                                    </p>
                                    {errors.is_public && <p className="mt-1 text-sm text-red-600">{errors.is_public}</p>}
                                </div>
                            </div>

                            {/* Form Actions */}
                            <div className="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right space-x-3 rounded-b-lg">
                                <button
                                    type="button"
                                    onClick={() => window.history.back()}
                                    className="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                >
                                    {t('common:cancel')}
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {processing ? t('common:saving') : (isEditing ? t('common:update') : t('common:create'))}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </DashboardLayout>
    );
}
