import React, { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import {
    ArrowLeftIcon,
    CheckIcon,
    XMarkIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    Cog6ToothIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';

export default function RolesForm({ role = null, permissions = [], isEdit = false }) {
    const { t } = useTranslation(['roles', 'common']);
    const [selectedPermissions, setSelectedPermissions] = useState([]);
    const [permissionGroups, setPermissionGroups] = useState({});

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: role?.name || '',
        description: role?.description || '',
        level: role?.level || 1,
        color: role?.color || '#3B82F6',
        active: role?.active ?? true,
        permissions: role?.permissions?.map(p => p.id) || [],
    });

    useEffect(() => {
        // Group permissions by prefix (e.g., users.view -> users)
        const groups = {};
        permissions.forEach(permission => {
            const parts = permission.name.split('.');
            const group = parts[0];
            if (!groups[group]) {
                groups[group] = [];
            }
            groups[group].push(permission);
        });
        setPermissionGroups(groups);
        setSelectedPermissions(data.permissions);
    }, [permissions, data.permissions]);

    const handleSubmit = (e) => {
        e.preventDefault();
        
        const submitData = {
            ...data,
            permissions: selectedPermissions,
        };

        if (isEdit) {
            put(route('roles.update', role.id), {
                onSuccess: () => reset(),
            });
        } else {
            post(route('roles.store'), {
                onSuccess: () => reset(),
            });
        }
    };

    const handlePermissionToggle = (permissionId) => {
        setSelectedPermissions(prev => {
            const newPermissions = prev.includes(permissionId)
                ? prev.filter(id => id !== permissionId)
                : [...prev, permissionId];
            
            setData('permissions', newPermissions);
            return newPermissions;
        });
    };

    const handleGroupToggle = (groupPermissions) => {
        const groupIds = groupPermissions.map(p => p.id);
        const allSelected = groupIds.every(id => selectedPermissions.includes(id));
        
        if (allSelected) {
            // Deselect all in group
            const newPermissions = selectedPermissions.filter(id => !groupIds.includes(id));
            setSelectedPermissions(newPermissions);
            setData('permissions', newPermissions);
        } else {
            // Select all in group
            const newPermissions = [...new Set([...selectedPermissions, ...groupIds])];
            setSelectedPermissions(newPermissions);
            setData('permissions', newPermissions);
        }
    };

    const getGroupIcon = (groupName) => {
        const icons = {
            users: UserGroupIcon,
            roles: ShieldCheckIcon,
            settings: Cog6ToothIcon,
            default: EyeIcon,
        };
        return icons[groupName] || icons.default;
    };

    const getGroupColor = (groupName) => {
        const colors = {
            users: 'text-blue-600 bg-blue-100',
            roles: 'text-purple-600 bg-purple-100',
            settings: 'text-gray-600 bg-gray-100',
            default: 'text-green-600 bg-green-100',
        };
        return colors[groupName] || colors.default;
    };

    const levelOptions = [
        { value: 1, label: t('roles:levels.basic'), color: 'text-gray-600' },
        { value: 2, label: t('roles:levels.intermediate'), color: 'text-blue-600' },
        { value: 3, label: t('roles:levels.advanced'), color: 'text-purple-600' },
        { value: 4, label: t('roles:levels.admin'), color: 'text-red-600' },
    ];

    return (
        <DashboardLayout>
            <Head title={isEdit ? t('roles:actions.edit') : t('roles:actions.create')} />
            
            <div className="py-6">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center space-x-4">
                        <Link
                            href={route('roles.index')}
                            className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors duration-200"
                        >
                            <ArrowLeftIcon className="w-4 h-4 mr-2" />
                            {t('common:back')}
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                                {isEdit ? t('roles:actions.edit') : t('roles:actions.create')}
                            </h1>
                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {isEdit 
                                    ? t('roles:messages.editDescription', { name: role?.name })
                                    : t('roles:messages.createDescription')
                                }
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Form */}
                        <div className="lg:col-span-2">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-6">
                                    {t('roles:sections.basicInfo')}
                                </h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Name */}
                                    <div>
                                        <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('roles:fields.name')} *
                                        </label>
                                        <input
                                            type="text"
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.name ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            placeholder={t('roles:placeholders.name')}
                                            disabled={processing}
                                        />
                                        {errors.name && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.name}</p>
                                        )}
                                    </div>

                                    {/* Level */}
                                    <div>
                                        <label htmlFor="level" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('roles:fields.level')} *
                                        </label>
                                        <select
                                            id="level"
                                            value={data.level}
                                            onChange={(e) => setData('level', parseInt(e.target.value))}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.level ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            disabled={processing}
                                        >
                                            {levelOptions.map(option => (
                                                <option key={option.value} value={option.value}>
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.level && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.level}</p>
                                        )}
                                    </div>

                                    {/* Description */}
                                    <div className="md:col-span-2">
                                        <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('roles:fields.description')}
                                        </label>
                                        <textarea
                                            id="description"
                                            rows={3}
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 ${
                                                errors.description ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600'
                                            }`}
                                            placeholder={t('roles:placeholders.description')}
                                            disabled={processing}
                                        />
                                        {errors.description && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.description}</p>
                                        )}
                                    </div>

                                    {/* Color */}
                                    <div>
                                        <label htmlFor="color" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('roles:fields.color')}
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
                                                {t('roles:fields.active')}
                                            </span>
                                        </label>
                                        <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {t('roles:help.activeStatus')}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Permissions */}
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-6">
                                    {t('roles:sections.permissions')}
                                </h3>
                                
                                <div className="space-y-6">
                                    {Object.entries(permissionGroups).map(([groupName, groupPermissions]) => {
                                        const GroupIcon = getGroupIcon(groupName);
                                        const groupColor = getGroupColor(groupName);
                                        const allSelected = groupPermissions.every(p => selectedPermissions.includes(p.id));
                                        const someSelected = groupPermissions.some(p => selectedPermissions.includes(p.id));
                                        
                                        return (
                                            <div key={groupName} className="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                                <div className="flex items-center justify-between mb-4">
                                                    <div className="flex items-center space-x-3">
                                                        <div className={`p-2 rounded-lg ${groupColor}`}>
                                                            <GroupIcon className="w-5 h-5" />
                                                        </div>
                                                        <div>
                                                            <h4 className="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                                                {t(`roles:groups.${groupName}`, groupName)}
                                                            </h4>
                                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                                {groupPermissions.length} {t('roles:fields.permissions')}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <button
                                                        type="button"
                                                        onClick={() => handleGroupToggle(groupPermissions)}
                                                        className={`px-3 py-1 text-xs font-medium rounded-full transition-colors duration-200 ${
                                                            allSelected
                                                                ? 'bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300'
                                                                : someSelected
                                                                ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-300'
                                                                : 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'
                                                        }`}
                                                    >
                                                        {allSelected ? t('common:deselectAll') : t('common:selectAll')}
                                                    </button>
                                                </div>
                                                
                                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    {groupPermissions.map(permission => (
                                                        <label
                                                            key={permission.id}
                                                            className="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                checked={selectedPermissions.includes(permission.id)}
                                                                onChange={() => handlePermissionToggle(permission.id)}
                                                                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                            />
                                                            <div className="ml-3">
                                                                <div className="text-sm font-medium text-gray-900 dark:text-white">
                                                                    {t(`permissions:${permission.name}`, permission.name)}
                                                                </div>
                                                                <div className="text-xs text-gray-500 dark:text-gray-400">
                                                                    {permission.name}
                                                                </div>
                                                            </div>
                                                        </label>
                                                    ))}
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                                
                                {errors.permissions && (
                                    <p className="mt-2 text-sm text-red-600 dark:text-red-400">{errors.permissions}</p>
                                )}
                            </div>
                        </div>

                        {/* Sidebar */}
                        <div className="lg:col-span-1">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    {t('roles:sections.summary')}
                                </h3>
                                
                                <div className="space-y-4">
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('roles:fields.name')}</div>
                                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                                            {data.name || t('common:notSet')}
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('roles:fields.level')}</div>
                                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                                            {levelOptions.find(l => l.value === data.level)?.label}
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('roles:fields.permissions')}</div>
                                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                                            {selectedPermissions.length} {t('common:selected')}
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">{t('roles:fields.status')}</div>
                                        <div className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                            data.active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                        }`}>
                                            {data.active ? (
                                                <>
                                                    <CheckIcon className="w-3 h-3 mr-1" />
                                                    {t('roles:status.active')}
                                                </>
                                            ) : (
                                                <>
                                                    <XMarkIcon className="w-3 h-3 mr-1" />
                                                    {t('roles:status.inactive')}
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
                                            href={route('roles.index')}
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
