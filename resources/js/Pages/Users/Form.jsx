import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { 
    UserIcon, 
    EnvelopeIcon, 
    PhoneIcon, 
    BuildingOfficeIcon,
    EyeIcon,
    EyeSlashIcon
} from '@heroicons/react/24/outline';

export default function UsersForm({ user, departments, roles, isEdit = false }) {
    const { t } = useTranslation(['users', 'common']);
    const [showPassword, setShowPassword] = useState(false);
    
    const { data, setData, post, put, processing, errors, reset } = useForm({
        first_name: user?.first_name || '',
        last_name: user?.last_name || '',
        email: user?.email || '',
        phone: user?.phone || '',
        password: '',
        password_confirmation: '',
        department_id: user?.department_id || '',
        roles: user?.roles?.map(role => role.id) || [],
        active: user?.active ?? true,
        locale: user?.locale || 'en',
        timezone: user?.timezone || 'UTC',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (isEdit) {
            put(route('users.update', user.id), {
                onSuccess: () => reset('password', 'password_confirmation')
            });
        } else {
            post(route('users.store'), {
                onSuccess: () => reset()
            });
        }
    };

    return (
        <DashboardLayout>
            <Head title={isEdit ? t('users:edit') : t('users:create')} />
            
            <div className="py-6">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8">
                        <h2 className="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl">
                            {isEdit ? t('users:edit') : t('users:create')}
                        </h2>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {isEdit ? t('users:editDescription') : t('users:createDescription')}
                        </p>
                    </div>

                    {/* Form */}
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div className="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                    {t('users:personalInfo')}
                                </h3>
                            </div>
                            
                            <div className="px-6 py-5 space-y-6">
                                {/* Name Fields */}
                                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {t('users:fields.firstName')} *
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                type="text"
                                                value={data.first_name}
                                                onChange={(e) => setData('first_name', e.target.value)}
                                                className={`block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm ${
                                                    errors.first_name ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                                                }`}
                                                placeholder={t('users:placeholders.firstName')}
                                            />
                                            <UserIcon className="absolute right-3 top-2 h-5 w-5 text-gray-400" />
                                        </div>
                                        {errors.first_name && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.first_name}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {t('users:fields.lastName')} *
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                type="text"
                                                value={data.last_name}
                                                onChange={(e) => setData('last_name', e.target.value)}
                                                className={`block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm ${
                                                    errors.last_name ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                                                }`}
                                                placeholder={t('users:placeholders.lastName')}
                                            />
                                        </div>
                                        {errors.last_name && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.last_name}</p>
                                        )}
                                    </div>
                                </div>

                                {/* Contact Fields */}
                                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {t('users:fields.email')} *
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                className={`block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm ${
                                                    errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                                                }`}
                                                placeholder={t('users:placeholders.email')}
                                            />
                                            <EnvelopeIcon className="absolute right-3 top-2 h-5 w-5 text-gray-400" />
                                        </div>
                                        {errors.email && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.email}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {t('users:fields.phone')}
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                type="tel"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                className={`block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm ${
                                                    errors.phone ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                                                }`}
                                                placeholder={t('users:placeholders.phone')}
                                            />
                                            <PhoneIcon className="absolute right-3 top-2 h-5 w-5 text-gray-400" />
                                        </div>
                                        {errors.phone && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.phone}</p>
                                        )}
                                    </div>
                                </div>

                                {/* Password Fields */}
                                {(!isEdit || data.password) && (
                                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {t('users:fields.password')} {!isEdit && '*'}
                                            </label>
                                            <div className="mt-1 relative">
                                                <input
                                                    type={showPassword ? 'text' : 'password'}
                                                    value={data.password}
                                                    onChange={(e) => setData('password', e.target.value)}
                                                    className={`block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-10 ${
                                                        errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                                                    }`}
                                                    placeholder={t('users:placeholders.password')}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => setShowPassword(!showPassword)}
                                                    className="absolute right-3 top-2 h-5 w-5 text-gray-400 hover:text-gray-600"
                                                >
                                                    {showPassword ? <EyeSlashIcon /> : <EyeIcon />}
                                                </button>
                                            </div>
                                            {errors.password && (
                                                <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.password}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {t('users:fields.passwordConfirmation')} {!isEdit && '*'}
                                            </label>
                                            <div className="mt-1">
                                                <input
                                                    type={showPassword ? 'text' : 'password'}
                                                    value={data.password_confirmation}
                                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                                    className={`block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm ${
                                                        errors.password_confirmation ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                                                    }`}
                                                    placeholder={t('users:placeholders.passwordConfirmation')}
                                                />
                                            </div>
                                            {errors.password_confirmation && (
                                                <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.password_confirmation}</p>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Organization & Permissions */}
                        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div className="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                    {t('users:organizationInfo')}
                                </h3>
                            </div>
                            
                            <div className="px-6 py-5 space-y-6">
                                {/* Department */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {t('users:fields.department')}
                                    </label>
                                    <div className="mt-1 relative">
                                        <select
                                            value={data.department_id}
                                            onChange={(e) => setData('department_id', e.target.value)}
                                            className="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="">{t('users:selectDepartment')}</option>
                                            {departments?.map((department) => (
                                                <option key={department.id} value={department.id}>
                                                    {department.name}
                                                </option>
                                            ))}
                                        </select>
                                        <BuildingOfficeIcon className="absolute right-8 top-2 h-5 w-5 text-gray-400 pointer-events-none" />
                                    </div>
                                    {errors.department_id && (
                                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.department_id}</p>
                                    )}
                                </div>

                                {/* Status */}
                                <div>
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={data.active}
                                            onChange={(e) => setData('active', e.target.checked)}
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            {t('users:fields.active')}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {/* Form Actions */}
                        <div className="flex justify-end space-x-3">
                            <button
                                type="button"
                                onClick={() => window.history.back()}
                                className="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                {t('common:cancel')}
                            </button>
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                {processing ? t('common:saving') : (isEdit ? t('common:update') : t('common:create'))}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </DashboardLayout>
    );
}
