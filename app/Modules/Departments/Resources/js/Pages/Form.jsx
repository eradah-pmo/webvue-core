import React from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { useTranslation } from "react-i18next";

export default function Form({ {{moduleName}} = null }) {
    const { t } = useTranslation();
    const isEditing = !!{{moduleName}};

    return (
        <DashboardLayout title={isEditing ? "Edit Departments" : "Create Departments"}>
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                        {isEditing ? "Edit Departments" : "Create Departments"}
                    </h1>
                </div>
                
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div className="p-6">
                        <p>Departments form interface</p>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}