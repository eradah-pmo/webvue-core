import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import {
    ArrowLeftIcon,
    CheckIcon,
    XMarkIcon,
    BuildingOfficeIcon,
    UserIcon,
    CurrencyDollarIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
} from '@heroicons/react/24/outline';

export default function DepartmentsForm({ 
    department = null, 
    parentDepartments = [], 
    managers = [], 
    isEdit = false 
}) {
    const { t } = useTranslation(['departments', 'common']);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: department?.name || '',
        description: department?.description || '',
        code: department?.code || '',
        parent_id: department?.parent_id || '',
        manager_id: department?.manager_id || '',
        email: department?.email || '',
        phone: department?.phone || '',
        address: department?.address || '',
        budget: department?.budget || '',
        color: department?.color || '#3B82F6',
        active: department?.active ?? true,
        sort_order: department?.sort_order || 1,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (isEdit) {
            put(route('departments.update', department.id), {
                onSuccess: () => reset(),
            });
        } else {
            post(route('departments.store'), {
                onSuccess: () => reset(),
            });
        }
    };

    const generateCode = () => {
        if (data.name) {
            const code = data.name
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '')
                .substring(0, 6);
            setData('code', code);
        }
    };

    return (
        <DashboardLayout>
            <Head title={isEdit ? t('departments:actions.edit') : t('departments:actions.create')} />
            
            <div className="py-6">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center space-x-4">
                        <Link
                            href={route('departments.index')}
                            className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors duration-200"
                        >
                            <ArrowLeftIcon className="w-4 h-4 mr-2" />
                            {t('common:back')}
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                                {isEdit ? t('departments:actions.edit') : t('departments:actions.create')}
                            </h1>
                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {isEdit 
                                    ? t('departments:messages.editDescription', { name: department?.name })
                                    : t('departments:messages.createDescription')
                                }
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Form */}
                        <div className="lg:col-span-2">
                            {/* Basic Information */}
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-6">
                                    {t('departments:sections.basicInfo')}
                                </h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Name */}
                                    <div>
                                        <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.name')} *
                                        </label>
                                        <input
                                            type="text"
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.name ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            placeholder={t('departments:placeholders.name')}
                                            disabled={processing}
                                        />
                                        {errors.name && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.name}</p>
                                        )}
                                    </div>

                                    {/* Code */}
                                    <div>
                                        <label htmlFor="code" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.code')}
                                        </label>
                                        <div className="flex">
                                            <input
                                                type="text"
                                                id="code"
                                                value={data.code}
                                                onChange={(e) => setData('code', e.target.value)}
                                                className={`flex-1 px-3 py-2 border rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                    errors.code ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                                }`}
                                                placeholder={t('departments:placeholders.code')}
                                                disabled={processing}
                                            />
                                            <button
                                                type="button"
                                                onClick={generateCode}
                                                className="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-600 dark:hover:bg-gray-500 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-lg text-sm text-gray-700 dark:text-gray-300 transition-colors duration-200"
                                                disabled={processing}
                                            >
                                                {t('departments:actions.generateCode')}
                                            </button>
                                        </div>
                                        {errors.code && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.code}</p>
                                        )}
                                    </div>

                                    {/* Parent Department */}
                                    <div>
                                        <label htmlFor="parent_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.parent')}
                                        </label>
                                        <select
                                            id="parent_id"
                                            value={data.parent_id}
                                            onChange={(e) => setData('parent_id', e.target.value)}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.parent_id ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            disabled={processing}
                                        >
                                            <option value="">{t('departments:placeholders.selectParent')}</option>
                                            {parentDepartments.map(dept => (
                                                <option key={dept.id} value={dept.id}>
                                                    {dept.full_name || dept.name}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.parent_id && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.parent_id}</p>
                                        )}
                                    </div>

                                    {/* Manager */}
                                    <div>
                                        <label htmlFor="manager_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.manager')}
                                        </label>
                                        <select
                                            id="manager_id"
                                            value={data.manager_id}
                                            onChange={(e) => setData('manager_id', e.target.value)}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.manager_id ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            disabled={processing}
                                        >
                                            <option value="">{t('departments:placeholders.selectManager')}</option>
                                            {managers.map(manager => (
                                                <option key={manager.id} value={manager.id}>
                                                    {manager.name} ({manager.email})
                                                </option>
                                            ))}
                                        </select>
                                        {errors.manager_id && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.manager_id}</p>
                                        )}
                                    </div>

                                    {/* Description */}
                                    <div className="md:col-span-2">
                                        <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.description')}
                                        </label>
                                        <textarea
                                            id="description"
                                            rows={3}
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.description ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            placeholder={t('departments:placeholders.description')}
                                            disabled={processing}
                                        />
                                        {errors.description && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.description}</p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Contact Information */}
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-6">
                                    {t('departments:sections.contactInfo')}
                                </h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Email */}
                                    <div>
                                        <label htmlFor="email" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.email')}
                                        </label>
                                        <div className="relative">
                                            <EnvelopeIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                            <input
                                                type="email"
                                                id="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                className={`w-full pl-10 pr-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                    errors.email ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                                }`}
                                                placeholder={t('departments:placeholders.email')}
                                                disabled={processing}
                                            />
                                        </div>
                                        {errors.email && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.email}</p>
                                        )}
                                    </div>

                                    {/* Phone */}
                                    <div>
                                        <label htmlFor="phone" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.phone')}
                                        </label>
                                        <div className="relative">
                                            <PhoneIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                            <input
                                                type="tel"
                                                id="phone"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                className={`w-full pl-10 pr-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                    errors.phone ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                                }`}
                                                placeholder={t('departments:placeholders.phone')}
                                                disabled={processing}
                                            />
                                        </div>
                                        {errors.phone && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.phone}</p>
                                        )}
                                    </div>

                                    {/* Address */}
                                    <div className="md:col-span-2">
                                        <label htmlFor="address" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.address')}
                                        </label>
                                        <div className="relative">
                                            <MapPinIcon className="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                            <textarea
                                                id="address"
                                                rows={2}
                                                value={data.address}
                                                onChange={(e) => setData('address', e.target.value)}
                                                className={`w-full pl-10 pr-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                    errors.address ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                                }`}
                                                placeholder={t('departments:placeholders.address')}
                                                disabled={processing}
                                            />
                                        </div>
                                        {errors.address && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.address}</p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Additional Settings */}
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-6">
                                    {t('departments:sections.additionalSettings')}
                                </h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Budget */}
                                    <div>
                                        <label htmlFor="budget" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.budget')}
                                        </label>
                                        <div className="relative">
                                            <CurrencyDollarIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                            <input
                                                type="number"
                                                step="0.01"
                                                id="budget"
                                                value={data.budget}
                                                onChange={(e) => setData('budget', e.target.value)}
                                                className={`w-full pl-10 pr-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                    errors.budget ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                                }`}
                                                placeholder={t('departments:placeholders.budget')}
                                                disabled={processing}
                                            />
                                        </div>
                                        {errors.budget && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.budget}</p>
                                        )}
                                    </div>

                                    {/* Sort Order */}
                                    <div>
                                        <label htmlFor="sort_order" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.sortOrder')}
                                        </label>
                                        <input
                                            type="number"
                                            id="sort_order"
                                            value={data.sort_order}
                                            onChange={(e) => setData('sort_order', e.target.value)}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.sort_order ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            placeholder={t('departments:placeholders.sortOrder')}
                                            disabled={processing}
                                        />
                                        {errors.sort_order && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.sort_order}</p>
                                        )}
                                    </div>

                                    {/* Color */}
                                    <div>
                                        <label htmlFor="color" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('departments:fields.color')}
                                        </label>
                                        <div className="flex items-center space-x-3">
                                            <input
                                                type="color"
                                                id="color"
                                                value={data.color}
                                                onChange={(e) => setData('color', e.target.value)}
                                                className="h-10 w-16 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer disabled:cursor-not-allowed"
                                                disabled={processing}
                                            />
                                            <input
                                                type="text"
                                                value={data.color}
                                                onChange={(e) => setData('color', e.target.value)}
                                                className="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                placeholder="#3B82F6"
                                                disabled={processing}
                                            />
                                        </div>
                                        {errors.color && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.color}</p>
                                        )}
                                    </div>

                                    {/* Active Status */}
                                    <div>
                                        <label className="flex items-center">
                                            <input
                                                type="checkbox"
                                                checked={data.active}
                                                onChange={(e) => setData('active', e.target.checked)}
                                                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:cursor-not-allowed"
                                                disabled={processing}
                                            />
                                            <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                {t('departments:fields.active')}
                                            </span>
                                        </label>
                                        <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {t('departments:help.activeStatus')}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Sidebar */}
                        <div className="lg:col-span-1">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    {t('departments:sections.summary')}
                                </h3>
                                
                                <div className="space-y-4">
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('departments:fields.name')}</div>
                                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                                            {data.name || t('common:notSet')}
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('departments:fields.code')}</div>
                                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                                            {data.code || t('common:notSet')}
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('departments:fields.parent')}</div>
                                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                                            {data.parent_id 
                                                ? parentDepartments.find(d => d.id == data.parent_id)?.name || t('common:notFound')
                                                : t('departments:labels.rootDepartment')
                                            }
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('departments:fields.status')}</div>
                                        <div className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                            data.active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                        }`}>
                                            {data.active ? (
                                                <>
                                                    <CheckIcon className="w-3 h-3 mr-1" />
                                                    {t('departments:status.active')}
                                                </>
                                            ) : (
                                                <>
                                                    <XMarkIcon className="w-3 h-3 mr-1" />
                                                    {t('departments:status.inactive')}
                                                </>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                
                                <div className="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div className="flex space-x-3">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                                        >
                                            {processing ? (
                                                <>
                                                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                                    {t('common:saving')}
                                                </>
                                            ) : (
                                                <>
                                                    <CheckIcon className="w-4 h-4 mr-2" />
                                                    {isEdit ? t('common:update') : t('common:create')}
                                                </>
                                            )}
                                        </button>
                                        
                                        <Link
                                            href={route('departments.index')}
                                            className="inline-flex justify-center items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200"
                                        >
                                            <XMarkIcon className="w-4 h-4 mr-2" />
                                            {t('common:cancel')}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
