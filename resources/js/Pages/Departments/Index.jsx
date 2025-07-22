import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
    PowerIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    ChevronRightIcon,
    ArrowsUpDownIcon,
} from '@heroicons/react/24/outline';
import { CheckIcon, XMarkIcon } from '@heroicons/react/24/solid';

export default function DepartmentsIndex({ departments, hierarchy, filters = {} }) {
    const { t } = useTranslation(['departments', 'common']);
    const [search, setSearch] = useState(filters.search || '');
    const [activeFilter, setActiveFilter] = useState(filters.active || '');
    const [parentFilter, setParentFilter] = useState(filters.parent_id || '');
    const [viewMode, setViewMode] = useState('table'); // table or tree

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('departments.index'), {
            search,
            active: activeFilter,
            parent_id: parentFilter,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleToggleStatus = (department) => {
        if (confirm(t('departments:messages.confirmToggleStatus', { name: department.name }))) {
            router.post(route('departments.toggle-status', department.id), {}, {
                preserveScroll: true,
            });
        }
    };

    const handleDelete = (department) => {
        if (confirm(t('departments:messages.confirmDelete', { name: department.name }))) {
            router.delete(route('departments.destroy', department.id));
        }
    };

    const handleMove = (department, newParentId) => {
        router.post(route('departments.move', department.id), { 
            parent_id: newParentId 
        }, {
            preserveScroll: true,
        });
    };

    const getHierarchyIndent = (level) => {
        return level * 24; // 24px per level
    };

    const renderDepartmentRow = (department, level = 0) => (
        <tr key={department.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
            <td className="px-6 py-4 whitespace-nowrap">
                <div className="flex items-center" style={{ paddingLeft: `${getHierarchyIndent(level)}px` }}>
                    {level > 0 && (
                        <ChevronRightIcon className="w-4 h-4 text-gray-400 mr-2" />
                    )}
                    <div className="flex items-center">
                        {department.color && (
                            <div 
                                className="w-3 h-3 rounded-full mr-3"
                                style={{ backgroundColor: department.color }}
                            ></div>
                        )}
                        <div>
                            <div className="text-sm font-medium text-gray-900 dark:text-white">
                                {department.name}
                            </div>
                            {department.code && (
                                <div className="text-sm text-gray-500 dark:text-gray-400">
                                    {department.code}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </td>
            <td className="px-6 py-4">
                <div className="text-sm text-gray-900 dark:text-white max-w-xs truncate">
                    {department.description || '-'}
                </div>
            </td>
            <td className="px-6 py-4 whitespace-nowrap">
                {department.parent ? (
                    <div className="text-sm text-gray-900 dark:text-white">
                        {department.parent.name}
                    </div>
                ) : (
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                        {t('departments:labels.rootDepartment')}
                    </span>
                )}
            </td>
            <td className="px-6 py-4 whitespace-nowrap">
                {department.manager ? (
                    <div className="text-sm text-gray-900 dark:text-white">
                        {department.manager.name}
                    </div>
                ) : (
                    <span className="text-sm text-gray-500 dark:text-gray-400">
                        {t('common:notAssigned')}
                    </span>
                )}
            </td>
            <td className="px-6 py-4 whitespace-nowrap">
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                    <UserGroupIcon className="w-3 h-3 mr-1" />
                    {department.users_count || 0}
                </span>
            </td>
            <td className="px-6 py-4 whitespace-nowrap">
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                    <BuildingOfficeIcon className="w-3 h-3 mr-1" />
                    {department.children_count || 0}
                </span>
            </td>
            <td className="px-6 py-4 whitespace-nowrap">
                <button
                    onClick={() => handleToggleStatus(department)}
                    className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors duration-200 ${
                        department.active
                            ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-300'
                            : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-300'
                    }`}
                >
                    {department.active ? (
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
                </button>
            </td>
            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div className="flex items-center justify-end space-x-2">
                    <Link
                        href={route('departments.show', department.id)}
                        className="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200"
                        title={t('common:view')}
                    >
                        <EyeIcon className="w-4 h-4" />
                    </Link>
                    
                    <Link
                        href={route('departments.edit', department.id)}
                        className="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 transition-colors duration-200"
                        title={t('common:edit')}
                    >
                        <PencilIcon className="w-4 h-4" />
                    </Link>
                    
                    {department.users_count === 0 && department.children_count === 0 && (
                        <button
                            onClick={() => handleDelete(department)}
                            className="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                            title={t('common:delete')}
                        >
                            <TrashIcon className="w-4 h-4" />
                        </button>
                    )}
                </div>
            </td>
        </tr>
    );

    const renderHierarchyTree = (departments, level = 0) => {
        return departments.map(department => (
            <React.Fragment key={department.id}>
                {renderDepartmentRow(department, level)}
                {department.children && department.children.length > 0 && 
                    renderHierarchyTree(department.children, level + 1)
                }
            </React.Fragment>
        ));
    };

    return (
        <DashboardLayout>
            <Head title={t('departments:title')} />
            
            <div className="py-6">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                            {t('departments:title')}
                        </h1>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {t('departments:subtitle')}
                        </p>
                    </div>
                    
                    <div className="flex items-center space-x-3">
                        <div className="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                            <button
                                onClick={() => setViewMode('table')}
                                className={`px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200 ${
                                    viewMode === 'table'
                                        ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                                }`}
                            >
                                {t('departments:views.table')}
                            </button>
                            <button
                                onClick={() => setViewMode('tree')}
                                className={`px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200 ${
                                    viewMode === 'tree'
                                        ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                                }`}
                            >
                                {t('departments:views.tree')}
                            </button>
                        </div>
                        
                        <Link
                            href={route('departments.create')}
                            className="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                        >
                            <PlusIcon className="w-4 h-4 mr-2" />
                            {t('departments:actions.create')}
                        </Link>
                    </div>
                </div>

                {/* Filters */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <form onSubmit={handleSearch} className="flex flex-wrap gap-4">
                        <div className="flex-1 min-w-64">
                            <div className="relative">
                                <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    type="text"
                                    placeholder={t('departments:filters.searchPlaceholder')}
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
                            <option value="">{t('departments:filters.allStatus')}</option>
                            <option value="1">{t('departments:status.active')}</option>
                            <option value="0">{t('departments:status.inactive')}</option>
                        </select>
                        
                        <select
                            value={parentFilter}
                            onChange={(e) => setParentFilter(e.target.value)}
                            className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">{t('departments:filters.allDepartments')}</option>
                            <option value="root">{t('departments:filters.rootOnly')}</option>
                            {hierarchy.map(dept => (
                                <option key={dept.id} value={dept.id}>
                                    {dept.name}
                                </option>
                            ))}
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

                {/* Departments Table */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead className="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('departments:fields.name')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('departments:fields.description')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('departments:fields.parent')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('departments:fields.manager')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('departments:fields.users')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('departments:fields.subDepartments')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('departments:fields.status')}
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t('common:actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {viewMode === 'tree' && hierarchy.length > 0 ? (
                                    renderHierarchyTree(hierarchy)
                                ) : (
                                    departments.data.map((department) => renderDepartmentRow(department))
                                )}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Pagination */}
                    {departments.links && viewMode === 'table' && (
                        <div className="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                            <div className="flex items-center justify-between">
                                <div className="flex-1 flex justify-between sm:hidden">
                                    {departments.prev_page_url && (
                                        <Link
                                            href={departments.prev_page_url}
                                            className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            {t('common:previous')}
                                        </Link>
                                    )}
                                    {departments.next_page_url && (
                                        <Link
                                            href={departments.next_page_url}
                                            className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            {t('common:next')}
                                        </Link>
                                    )}
                                </div>
                                <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm text-gray-700 dark:text-gray-300">
                                            {t('common:showing')} {departments.from} {t('common:to')} {departments.to} {t('common:of')} {departments.total} {t('common:results')}
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
