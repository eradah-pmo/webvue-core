import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from '@inertiajs/react';
import {
    PlusIcon,
    UserPlusIcon,
    DocumentPlusIcon,
    CogIcon,
    ShieldCheckIcon,
    BuildingOfficeIcon,
    ArrowTopRightOnSquareIcon,
    CommandLineIcon,
} from '@heroicons/react/24/outline';

const QuickActionCard = ({ action, index }) => {
    const { t } = useTranslation();
    const [isHovered, setIsHovered] = useState(false);

    const getActionIcon = (type) => {
        const icons = {
            user: UserPlusIcon,
            document: DocumentPlusIcon,
            settings: CogIcon,
            security: ShieldCheckIcon,
            department: BuildingOfficeIcon,
            general: PlusIcon,
            command: CommandLineIcon,
        };
        return icons[type] || PlusIcon;
    };

    const getGradientClass = (color) => {
        const gradients = {
            blue: 'from-blue-500 to-blue-600',
            green: 'from-green-500 to-green-600',
            purple: 'from-purple-500 to-purple-600',
            yellow: 'from-yellow-500 to-yellow-600',
            red: 'from-red-500 to-red-600',
            indigo: 'from-indigo-500 to-indigo-600',
            pink: 'from-pink-500 to-pink-600',
        };
        return gradients[color] || gradients.blue;
    };

    const Icon = getActionIcon(action.type);

    const cardContent = (
        <div 
            className={`
                relative group cursor-pointer rounded-xl bg-white dark:bg-gray-800 
                border border-gray-200 dark:border-gray-700 p-6 
                hover:shadow-xl hover:scale-105 transition-all duration-300 transform
                ${isHovered ? 'shadow-lg' : 'shadow-sm'}
            `}
            style={{ animationDelay: `${index * 100}ms` }}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            {/* Background Gradient Overlay */}
            <div className={`
                absolute inset-0 bg-gradient-to-br ${getGradientClass(action.color)} 
                opacity-0 group-hover:opacity-5 transition-opacity duration-300 rounded-xl
            `} />
            
            {/* Content */}
            <div className="relative z-10">
                <div className="flex items-center justify-between mb-4">
                    <div className={`
                        w-12 h-12 rounded-lg bg-gradient-to-br ${getGradientClass(action.color)}
                        flex items-center justify-center shadow-lg group-hover:scale-110 
                        transition-transform duration-300
                    `}>
                        <Icon className="w-6 h-6 text-white" />
                    </div>
                    
                    {action.external && (
                        <ArrowTopRightOnSquareIcon className="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors duration-200" />
                    )}
                </div>

                <div className="space-y-2">
                    <h3 className="font-semibold text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors duration-200">
                        {action.title}
                    </h3>
                    <p className="text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300 transition-colors duration-200">
                        {action.description}
                    </p>
                </div>

                {/* Badge */}
                {action.badge && (
                    <div className="mt-4">
                        <span className={`
                            inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            ${action.badge.type === 'new' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
                            }
                        `}>
                            {action.badge.text}
                        </span>
                    </div>
                )}

                {/* Keyboard Shortcut */}
                {action.shortcut && (
                    <div className="mt-3 flex items-center space-x-1 text-xs text-gray-500 dark:text-gray-400">
                        <CommandLineIcon className="w-3 h-3" />
                        <span>{action.shortcut}</span>
                    </div>
                )}
            </div>
        </div>
    );

    if (action.href) {
        return (
            <Link href={action.href} className="block">
                {cardContent}
            </Link>
        );
    }

    if (action.onClick) {
        return (
            <button onClick={action.onClick} className="block w-full text-left">
                {cardContent}
            </button>
        );
    }

    return cardContent;
};

export default function QuickActions({ actions = [], title, loading = false }) {
    const { t } = useTranslation();

    const defaultActions = [
        {
            id: 'add-user',
            title: t('dashboard:quick_actions.add_user'),
            description: t('dashboard:quick_actions.add_user_desc'),
            type: 'user',
            color: 'blue',
            href: '/users/create',
            shortcut: 'Ctrl+N',
        },
        {
            id: 'create-role',
            title: t('dashboard:quick_actions.create_role'),
            description: t('dashboard:quick_actions.create_role_desc'),
            type: 'security',
            color: 'purple',
            href: '/roles/create',
        },
        {
            id: 'add-department',
            title: t('dashboard:quick_actions.add_department'),
            description: t('dashboard:quick_actions.add_department_desc'),
            type: 'department',
            color: 'green',
            href: '/departments/create',
        },
        {
            id: 'system-settings',
            title: t('dashboard:quick_actions.system_settings'),
            description: t('dashboard:quick_actions.system_settings_desc'),
            type: 'settings',
            color: 'indigo',
            href: '/settings',
        },
        {
            id: 'view-logs',
            title: t('dashboard:quick_actions.view_logs'),
            description: t('dashboard:quick_actions.view_logs_desc'),
            type: 'document',
            color: 'yellow',
            href: '/audit-logs',
            badge: { type: 'info', text: t('dashboard:quick_actions.security') }
        },
        {
            id: 'backup-system',
            title: t('dashboard:quick_actions.backup_system'),
            description: t('dashboard:quick_actions.backup_system_desc'),
            type: 'command',
            color: 'red',
            onClick: () => console.log('Backup initiated'),
            badge: { type: 'new', text: t('dashboard:quick_actions.admin_only') }
        },
    ];

    const displayActions = actions.length > 0 ? actions : defaultActions;

    if (loading) {
        return (
            <div className="space-y-6">
                <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/4 animate-pulse" />
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {[...Array(6)].map((_, index) => (
                        <div key={index} className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-pulse">
                            <div className="flex items-center justify-between mb-4">
                                <div className="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg" />
                            </div>
                            <div className="space-y-2">
                                <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
                                <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full" />
                                <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3" />
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                    {title || t('dashboard:quick_actions.title')}
                </h2>
                <p className="text-sm text-gray-600 dark:text-gray-400">
                    {t('dashboard:quick_actions.subtitle')}
                </p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {displayActions.map((action, index) => (
                    <QuickActionCard 
                        key={action.id} 
                        action={action} 
                        index={index} 
                    />
                ))}
            </div>
        </div>
    );
}
