<?php

namespace App\Traits;

trait AccessTrait
{
    // All permissions flat by module name
    public static $permissions = [
        "Dashboard" => [
            "View Dashboard",
            "View Dashboard Cards",
            "View Dashboard Charts",
            "View Dashboard Tables",
        ],

        "Entities" => [
            'View Entities', 'Edit Entities', 'Add Entities', 'Delete Entities',
        ],

        "Staff" => [
            'View Staff', 'Edit Staff', 'Add Staff', 'Delete Staff', 'Assign Roles',
        ],

        "Reports" => [
            'View Report', 'Edit Report', 'Add Report', 'Delete Report',
        ],

        "Logs" => [
            'View Logs',
        ],

        "Contractor" => [
            "View Contractor", "Edit Contractor", "Add Contractor",
        ],

        "Products" => [
            'View Products', 'Edit Products', 'Add Products', 'Delete Products',
        ],

        "Sales" => [
            'View Sales', 'Edit Sales', 'Add Sales', 'Delete Sales',
        ],

        "Clients" => [
            'View Clients', 'Edit Clients', 'Add Clients', 'Delete Clients',
        ],

        "Customers" => [
            'View Queues',
        ],

        "Withdrawals" => [
            'View Withdrawals', 'Edit Withdrawals', 'Add Withdrawals', 'Delete Withdrawals',
        ],

        "Modules" => [
            'View Modules', 'Edit Modules', 'Add Modules', 'Delete Modules',
        ],

        "Stock" => [
            'View Stock', 'Edit Stock', 'Add Stock', 'Delete Stock',
        ],

        "Service Points" => [
            'View Service Points', 'Edit Service Points', 'Add Service Points', 'Delete Service Points', 'Bulky Update Service Points',
        ],

        "Departments" => [
            'View Departments', 'Edit Departments', 'Add Departments', 'Delete Departments', 'Bulky Update Departments',
        ],

        "Qualifications" => [
            'View Qualifications', 'Edit Qualifications', 'Add Qualifications', 'Delete Qualifications', 'Bulky Update Qualifications',
        ],

        "Titles" => [
            'View Titles', 'Edit Titles', 'Add Titles', 'Delete Titles', 'Bulky Update Titles',
        ],

        "Rooms" => [
            'View Rooms', 'Edit Rooms', 'Add Rooms', 'Delete Rooms', 'Bulky Update Rooms',
        ],

        "Sections" => [
            'View Sections', 'Edit Sections', 'Add Sections', 'Delete Sections', 'Bulky Update Sections',
        ],

        "Item Units" => [
            'View Item Units', 'Edit Item Units', 'Add Item Units', 'Delete Item Units', 'Bulky Update Item Units',
        ],

        "Groups" => [
            'View Groups', 'Edit Groups', 'Add Groups', 'Delete Groups', 'Bulky Update Groups',
        ],

        "Patient Categories" => [
            'View Patient Categories', 'Edit Patient Categories', 'Add Patient Categories', 'Delete Patient Categories', 'Bulky Update Patient Categories',
        ],

        "Suppliers" => [
            'View Suppliers', 'Edit Suppliers', 'Add Suppliers', 'Delete Suppliers', 'Bulky Update Suppliers',
        ],

        "Stores" => [
            'View Stores', 'Edit Stores', 'Add Stores', 'Delete Stores', 'Bulky Update Stores',
        ],

        "Insurance Companies" => [
            'View Insurance Companies', 'Edit Insurance Companies', 'Add Insurance Companies', 'Delete Insurance Companies', 'Bulky Update Insurance Companies',
        ],

        "Sub Groups" => [
            'View Sub Groups', 'Edit Sub Groups', 'Add Sub Groups', 'Delete Sub Groups', 'Bulky Update Sub Groups',
        ],

        "Admin Users" => [
            'View Admin Users', 'Edit Admin Users', 'Add Admin Users', 'Delete Admin Users', 'Assign Roles',
        ],

        "Audit Logs" => [
            'View Audit Logs',
        ],

        "System Settings" => [
            'View System Settings', 'Edit System Settings',
        ],

        "Business" => [
            'View Business', 'Edit Business', 'Add Business', 'Delete Business',
        ],

        "Branches" => [
            'View Branches', 'Edit Branches', 'Add Branches', 'Delete Branches',
        ],

        "Report Access" => [
            'View Reports', 'Export Reports', 'Filter Reports',
        ],
    ];

    /**
     * Get all permissions, optionally excluding some modules
     * 
     * @param array $exclude List of module keys to exclude
     * @return array Filtered permissions array
     */
    public static function getAccessControl(array $exclude = []): array
    {
        if (empty($exclude)) {
            return self::$permissions;
        }

        return array_filter(
            self::$permissions,
            fn($key) => !in_array($key, $exclude),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Check if a user has a permission.
     *
     * @param string $permission Permission string to check
     * @param string $userPermissionsJson JSON-encoded string of user permissions
     * @return bool True if user has permission
     */
    public static function userCan(string $permission, string $userPermissionsJson): bool
    {
        $userPermissions = json_decode($userPermissionsJson, true) ?: [];
        return in_array($permission, $userPermissions);
    }
}
