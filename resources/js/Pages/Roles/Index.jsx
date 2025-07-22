import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
    DocumentDuplicateIcon,
    PowerIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
} from '@heroicons/react/24/outline';
import { CheckIcon, XMarkIcon } from '@heroicons/react/24/solid';

export default function RolesIndex({ roles, filters = {} }) {
    const { t } = useTranslation(['roles', 'common']);
    const [search, setSearch] = useState(filters.search || '');
    const [activeFilter, setActiveFilter] = useState(filters.active || '');
    const [levelFilter, setLevelFilter] = useState(filters.level || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('roles.index'), {
            search,
            active: activeFilter,
            level: levelFilter,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleToggleStatus = (role) => {
        if (confirm(t('roles:messages.confirmToggleStatus', { name: role.name }))) {
            router.post(route('roles.toggle-status', role.id), {}, {
                preserveScroll: true,
            });
        }
    };

    const handleDelete = (role) => {
        if (confirm(t('roles:messages.confirmDelete', { name: role.name }))) {
            router.delete(route('roles.destroy', role.id));
        }
    };

    const handleDuplicate = (role) => {
        const newName = prompt(t('roles:messages.duplicateName'), role.name + ' Copy');
        if (newName) {
            router.post(route('roles.duplicate', role.id), { name: newName });
        }
    };

    const getRoleLevelBadge = (level) => {
        const levels = {
            1: { color: 'bg-gray-100 text-gray-800', text: t('roles:levels.basic') },
            2: { color: 'bg-blue-100 text-blue-800', text: t('roles:levels.intermediate') },
            3: { color: 'bg-purple-100 text-purple-800', text: t('roles:levels.advanced') },
            4: { color: 'bg-red-100 text-red-800', text: t('roles:levels.admin') },
        };
        
        const levelInfo = levels[level] || levels[1];
        
        return (
            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${levelInfo.color}`}>
                {levelInfo.text}
            </span>
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('roles:title')} />
            
            <div className="py-6">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                            {t('roles:title')}
                        </h1>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {t('roles:subtitle')}
                        </p>
                    </div>
                    
                    <Link
                        href={route('roles.create')}
                        className="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                    >
                        <PlusIcon className="w-4 h-4 mr-2" />
                        {t('roles:actions.create')}
                    </Link>
                </div>

                {/* Filters */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <form onSubmit={handleSearch} className="flex flex-wrap gap-4">
                        <div className="flex-1 min-w-64">
                            <div className="relative">
                                <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    type="text"
                                    placeholder={t('roles:filters.searchPlaceholder')}
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>
                        
                        <select
                            value={activeFilter}
                            onChange={(e) => setActiveFilter(e.target.value)}
                            className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">{t('roles:filters.allStatus')}</option>
                            <option value="1">{t('roles:status.active')}</option>
                            <option value="0">{t('roles:status.inactive')}</option>
                        </select>
                        
                        <select
                            value={levelFilter}
                            onChange={(e) => setLevelFilter(e.target.value)}
                            className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">{t('roles:filters.allLevels')}</option>
                            <option value="1">{t('roles:levels.basic')}</option>
                            <option value="2">{t('roles:levels.intermediate')}</option>
                            <option value="3">{t('roles:levels.advanced')}</option>
                            <option value="4">{t('roles:levels.admin')}</option>
                        </select>
                        
                        <button
                            type="submit"
                            className="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200"
                        >
                            <FunnelIcon className="w-4 h-4 mr-2" />
                            {t('common:filter')}
                        </button>
                    </form>
                </div>

                {/* Roles Table */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead className="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('roles:fields.name')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('roles:fields.description')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('roles:fields.level')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('roles:fields.permissions')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('roles:fields.users')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('roles:fields.status')}
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('common:actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {roles.data.map((role) => (
                                    <tr key={role.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center">
                                                {role.color && (
                                                    <div 
                                                        className="w-3 h-3 rounded-full mr-3"
                                                        style={{ backgroundColor: role.color }}
                                                    ></div>
                                                )}
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900 dark:text-white">
                                                        {role.display_name}
                                                    </div>
                                                    <div className="text-sm text-gray-500 dark:text-gray-400">
                                                        {role.name}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="text-sm text-gray-900 dark:text-white max-w-xs truncate">
                                                {role.description || '-'}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getRoleLevelBadge(role.level)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                {role.permissions_count || 0} {t('roles:fields.permissions')}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                {role.users_count || 0} {t('roles:fields.users')}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <button
                                                onClick={() => handleToggleStatus(role)}
                                                className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors duration-200 ${
                                                    role.active
                                                        ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-300'
                                                        : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-300'
                                                }`}
                                            >
                                                {role.active ? (
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
                                            </button>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div className="flex items-center justify-end space-x-2">
                                                <Link
                                                    href={route('roles.show', role.id)}
                                                    className="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200"
                                                    title={t('common:view')}
                                                >
                                                    <EyeIcon className="w-4 h-4" />
                                                </Link>
                                                
                                                <Link
                                                    href={route('roles.edit', role.id)}
                                                    className="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 transition-colors duration-200"
                                                    title={t('common:edit')}
                                                >
                                                    <PencilIcon className="w-4 h-4" />
                                                </Link>
                                                
                                                <button
                                                    onClick={() => handleDuplicate(role)}
                                                    className="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 transition-colors duration-200"
                                                    title={t('roles:actions.duplicate')}
                                                >
                                                    <DocumentDuplicateIcon className="w-4 h-4" />
                                                </button>
                                                
                                                {role.name !== 'super-admin' && (
                                                    <button
                                                        onClick={() => handleDelete(role)}
                                                        className="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                                                        title={t('common:delete')}
                                                        disabled={role.users_count > 0}
                                                    >
                                                        <TrashIcon className="w-4 h-4" />
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Pagination */}
                    {roles.links && (
                        <div className="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                            <div className="flex items-center justify-between">
                                <div className="flex-1 flex justify-between sm:hidden">
                                    {roles.prev_page_url && (
                                        <Link
                                            href={roles.prev_page_url}
                                            className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            {t('common:previous')}
                                        </Link>
                                    )}
                                    {roles.next_page_url && (
                                        <Link
                                            href={roles.next_page_url}
                                            className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            {t('common:next')}
                                        </Link>
                                    )}
                                </div>
                                <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm text-gray-700 dark:text-gray-300">
                                            {t('common:showing')} {roles.from} {t('common:to')} {roles.to} {t('common:of')} {roles.total} {t('common:results')}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </DashboardLayout>
    );
}
