import React from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { useTranslation } from "react-i18next";

export default function Index({ {{moduleName}}s }) {
    const { t } = useTranslation();

    return (
        <DashboardLayout title="Userss">
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                        Userss
                    </h1>
                </div>
                
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div className="p-6">
                        <p>Users management interface</p>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}