import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { useTranslation } from 'react-i18next';
import {
    ArrowLeftIcon,
    EyeIcon,
    EyeSlashIcon,
    DocumentArrowUpIcon,
} from '@heroicons/react/24/outline';
import { toast } from 'react-hot-toast';

export default function Form({ setting = null, categories = [] }) {
    const { t } = useTranslation();
    const isEditing = !!setting;
    const [showValue, setShowValue] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        key: setting?.key || '',
        category: setting?.category || 'general',
        value: setting?.value || '',
        type: setting?.type || 'string',
        description: setting?.description || '',
        is_public: setting?.is_public || false,
        is_encrypted: setting?.is_encrypted || false,
        sort_order: setting?.sort_order || 0,
        active: setting?.active ?? true,
        file: null,
    });

    const settingTypes = [
        { value: 'string', label: t('settings.string') },
        { value: 'number', label: t('settings.number') },
        { value: 'boolean', label: t('settings.boolean') },
        { value: 'json', label: t('settings.json') },
        { value: 'file', label: t('settings.file') },
    ];

    const allCategories = ['general', 'security', 'mail', 'ui', 'files', 'notifications', 'backup'];

    const handleSubmit = (e) => {
        e.preventDefault();
        
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(isEditing ? t('settings.updated_successfully') : t('settings.created_successfully'));
            },
            onError: () => {
                toast.error(t('settings.operation_failed'));
            },
        };

        if (isEditing) {
            post(route('settings.update', setting.id), options);
        } else {
            post(route('settings.store'), options);
        }
    };

    const renderValueInput = () => {
        switch (data.type) {
            case 'boolean':
                return (
                    <div className="flex items-center space-x-3">
                        <label className="flex items-center">
                            <input
                                type="radio"
                                name="value"
                                checked={data.value === true || data.value === 'true'}
                                onChange={() => setData('value', true)}
                                className="h-4 w-4 text-blue-600 focus:ring-blue-500"
                            />
                            <span className="ml-2 text-sm">{t('common.yes')}</span>
                        </label>
                        <label className="flex items-center">
                            <input
                                type="radio"
                                name="value"
                                checked={data.value === false || data.value === 'false'}
                                onChange={() => setData('value', false)}
                                className="h-4 w-4 text-blue-600 focus:ring-blue-500"
                            />
                            <span className="ml-2 text-sm">{t('common.no')}</span>
                        </label>
                    </div>
                );
            case 'number':
                return (
                    <input
                        type="number"
                        value={data.value}
                        onChange={(e) => setData('value', e.target.value)}
                        className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                );
            case 'json':
                return (
                    <textarea
                        value={typeof data.value === 'string' ? data.value : JSON.stringify(data.value, null, 2)}
                        onChange={(e) => setData('value', e.target.value)}
                        rows={6}
                        className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                    />
                );
            case 'file':
                return (
                    <div>
                        <input
                            type="file"
                            onChange={(e) => setData('file', e.target.files[0])}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        />
                        {setting?.value && (
                            <p className="mt-2 text-sm text-gray-600">
                                Current: <a href={`/storage/${setting.value}`} target="_blank" className="text-blue-600 underline">View File</a>
                            </p>
                        )}
                    </div>
                );
            default:
                return (
                    <div className="relative">
                        <input
                            type={data.is_encrypted ? (showValue ? 'text' : 'password') : 'text'}
                            value={data.value}
                            onChange={(e) => setData('value', e.target.value)}
                            className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        />
                        {data.is_encrypted && (
                            <button
                                type="button"
                                onClick={() => setShowValue(!showValue)}
                                className="absolute right-3 top-2"
                            >
                                {showValue ? <EyeSlashIcon className="h-5 w-5" /> : <EyeIcon className="h-5 w-5" />}
                            </button>
                        )}
                    </div>
                );
        }
    };

    return (
        <DashboardLayout>
            <Head title={isEditing ? t('settings.edit_setting') : t('settings.create_setting')} />
            
            <div className="space-y-6">
                <div className="flex items-center space-x-3">
                    <Link href={route('settings.index')} className="text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon className="h-5 w-5" />
                    </Link>
                    <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                        {isEditing ? t('settings.edit_setting') : t('settings.create_setting')}
                    </h1>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div className="space-y-6">
                            <div>
                                <label className="block text-sm font-medium mb-2">{t('settings.key')} *</label>
                                <input
                                    type="text"
                                    value={data.key}
                                    onChange={(e) => setData('key', e.target.value)}
                                    className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    required
                                />
                                {errors.key && <p className="mt-1 text-sm text-red-600">{errors.key}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">{t('settings.category')} *</label>
                                <select
                                    value={data.category}
                                    onChange={(e) => setData('category', e.target.value)}
                                    className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                    {allCategories.map((category) => (
                                        <option key={category} value={category}>
                                            {t(`settings.${category}`)}
                                        </option>
                                    ))}
                                </select>
                                {errors.category && <p className="mt-1 text-sm text-red-600">{errors.category}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">{t('settings.type')} *</label>
                                <select
                                    value={data.type}
                                    onChange={(e) => setData('type', e.target.value)}
                                    className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                    {settingTypes.map((type) => (
                                        <option key={type.value} value={type.value}>
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                                {errors.type && <p className="mt-1 text-sm text-red-600">{errors.type}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">{t('settings.description')}</label>
                                <textarea
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    rows={3}
                                    className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                />
                                {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">{t('settings.value')}</label>
                                {renderValueInput()}
                                {errors.value && <p className="mt-1 text-sm text-red-600">{errors.value}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={data.is_public}
                                        onChange={(e) => setData('is_public', e.target.checked)}
                                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <label className="ml-2 text-sm">{t('settings.is_public')}</label>
                                </div>

                                <div className="flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={data.is_encrypted}
                                        onChange={(e) => setData('is_encrypted', e.target.checked)}
                                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <label className="ml-2 text-sm">{t('settings.is_encrypted')}</label>
                                </div>

                                <div className="flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={data.active}
                                        onChange={(e) => setData('active', e.target.checked)}
                                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <label className="ml-2 text-sm">{t('settings.active')}</label>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium mb-1">{t('settings.sort_order')}</label>
                                    <input
                                        type="number"
                                        value={data.sort_order}
                                        onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                        className="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end space-x-3">
                        <Link
                            href={route('settings.index')}
                            className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            {t('common.cancel')}
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            {processing ? t('common.saving') : (isEditing ? t('common.update') : t('common.create'))}
                        </button>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}