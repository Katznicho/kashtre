<?php

namespace App\Traits;

trait AccessTrait
{
    public static $admin = [
        "Dashboard" => [
            "View Dashboard", "View Dashboard Cards", "View Dashboard Charts",
        ]
    ];

    public static $entities = [
        "Entities" => ['View Entities', 'Edit Entities', 'Add Entities'],
    ];

    public static $departments = [
        "Departments" => ['View Departments', 'Edit Departments', 'Add Departments'],
    ];

    public static $staff = [
        "Staff" => ['View Staff', 'Edit Staff', 'Add Staff', 'Assign Roles'],
    ];

    public static $reports = [
        "Reports" => ['View Report', 'Edit Report', 'Add Report'],
    ];

    public static $logs = [
        "Logs" => ['View Logs'],
    ];

    public static $contractor = [
        "Contractor" => ["View Contractor"],
    ];

   

    public static $sales = [
        "Sales" => ['View Sales', 'Edit Sales', 'Add Sales'],
    ];

    public static $clients = [
        "Clients" => ['View Clients', 'Edit Clients', 'Add Clients'],
    ];

    public static $queues = [
        "Customers" => ['View Queues'],
    ];

    public static $withdrawaal = [
        "Withdrawals" => ['View Withdrawals', 'Edit Withdrawals', 'Add Withdrawals'],
    ];


    public static $modules = [
        "Modules" => ['View Modules', 'Edit Modules', 'Add Modules'],
    ];

    public static $stock = [
        "Stock" => ['View Stock', 'Edit Stock', 'Add Stock'],
    ];

    public static $masters = [
        "Service Points" => ['View Service Points', 'Edit Service Points', 'Add Service Points', 'Bulky Update Service Points'],
        "Service Charges" => ['Manage Service Charges'],
        "Contractor Service Charges" => ['Manage Contractor Service Charges'],
        "Departments" => ['View Departments', 'Edit Departments', 'Add Departments', 'Bulky Update Departments'],
        "Qualifications" => ['View Qualifications', 'Edit Qualifications', 'Add Qualifications', 'Bulky Update Qualifications'],
        "Titles" => ['View Titles', 'Edit Titles', 'Add Titles', 'Bulky Update Titles'],
        "Rooms" => ['View Rooms', 'Edit Rooms', 'Add Rooms', 'Bulky Update Rooms'],
        "Sections" => ['View Sections', 'Edit Sections', 'Add Sections', 'Bulky Update Sections'],
        "Item Units" => ['View Item Units', 'Edit Item Units', 'Add Item Units', 'Bulky Update Item Units'],
        "Groups" => ['View Groups', 'Edit Groups', 'Add Groups', 'Bulky Update Groups'],
        "Patient Categories" => ['View Patient Categories', 'Edit Patient Categories', 'Add Patient Categories', 'Bulky Update Patient Categories'],
        "Suppliers" => ['View Suppliers', 'Edit Suppliers', 'Add Suppliers', 'Bulky Update Suppliers'],
        "Stores" => ['View Stores', 'Edit Stores', 'Add Stores', 'Bulky Update Stores'],
        "Insurance Companies" => ['View Insurance Companies', 'Edit Insurance Companies', 'Add Insurance Companies', 'Bulky Update Insurance Companies'],
        "Sub Groups" => ['View Sub Groups', 'Edit Sub Groups', 'Add Sub Groups', 'Bulky Update Sub Groups'],
        "Maturation Periods" => ['View Maturation Periods', 'Edit Maturation Periods', 'Add Maturation Periods', 'Manage Maturation Periods'],
        "Withdrawal Settings" => ['View Withdrawal Settings', 'Edit Withdrawal Settings', 'Add Withdrawal Settings'],
        "Business Withdrawal Settings" => ['View Business Withdrawal Settings', 'Edit Business Withdrawal Settings', 'Add Business Withdrawal Settings'],
    ];

    public static $adminAccess = [
        "Admin Users" => ['View Admin Users', 'Edit Admin Users', 'Add Admin Users', 'Assign Roles', 'Bulk Admin Upload'],
        "Audit Logs" => ['View Audit Logs'],
        "System Settings" => ['View System Settings', 'Edit System Settings'],
    ];

    public static $businessAccess = [
        "Business" => ['View Business', 'Edit Business', 'Add Business'],
        "Branches" => ['View Branches', 'Edit Branches', 'Add Branches'],
    ];

    public static $clientAccess = [
        "Clients" => ['View Clients', 'Edit Clients', 'Add Clients'],
    ];

    public static $staffAccess = [
        "Staff" => [
            'View Staff', 'Edit Staff', 'Add Staff', 'Assign Roles',
            "Edit Contractor", "Add Contractor Profile", 'View Contractor Profile', 'Edit Contractor Profile'
    ],
    ];

    public static $reportAccess = [
        "Reports" => ['View Reports', 'Export Reports', 'Filter Reports'],
    ];

    public static $bulkUpload = [
        "Bulk Upload" => ['Bulk Validations Upload'],
    ];

    public static $items = [
        "Items" => ['View Items', 'Edit Items', 'Add Items', 'Bulk Upload Items'],
    ];

    public static $finance = [
                    "Finance" => ['View Finance', 'Manage Finance', 'View Business Balance Statement', 'View Client Balance Statement', 'View Money Tracking', 'View Withdrawal Requests', 'Manage Withdrawal Requests'],
    ];

    public static $packageTracking = [
        "Package Tracking" => ['View Package Tracking', 'Edit Package Tracking', 'Add Package Tracking', 'View Package History'],
    ];

    public static $packageSales = [
        "Package Sales" => ['View Package Sales', 'Edit Package Sales', 'Add Package Sales', 'View Package Sales History', 'Export Package Sales'],
    ];


    public static function spreadArrayKeys($assocArray)
    {
        $result = [];
        foreach ($assocArray as $key => $value) {
            if (is_string($key)) {
                $result[] = $key;
            }
            if (is_array($value)) {
                $result = array_merge($result, static::spreadArrayKeys($value));
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }

    public static function getAllPermissions()
    {
        $roles = static::spreadArrayKeys(
            array_merge(
                static::$admin,
                static::$entities,
                static::$items,
                static::$staff,
                static::$reports,
                static::$logs,
                static::$contractor,
                static::$sales,
                static::$clients,
                static::$queues,
                static::$withdrawaal,
                static::$modules,
                static::$stock,
                static::$masters,
                static::$adminAccess,
                static::$businessAccess,
                static::$clientAccess,
                static::$staffAccess,
                static::$reportAccess,
                static::$bulkUpload,
                static::$finance,
                static::$packageTracking,
                static::$packageSales
            )
        );
        return $roles;
    }

    public static function getAccessControl(array $exclude = [])
{
    $permissions = [
        "Dashboard" => self::$admin,
        "Entities" => self::$entities,
        "Items" => self::$items,
        "Staff" => self::$staff,
        "Reports" => self::$reports,
        "Logs" => self::$logs,
        "Contractor" => self::$contractor,
        "Sales" => self::$sales,
        "Clients" => self::$clients,
        "Queues" => self::$queues,
        "Withdrawals" => self::$withdrawaal,
        "Modules" => self::$modules,
        "Stock" => self::$stock,
        "Masters" => self::$masters,
        "Admin" => self::$adminAccess,
        "Business" => self::$businessAccess,
        "Client" => self::$clientAccess,
        "Staff Access" => self::$staffAccess,
        "Report Access" => self::$reportAccess,
        "Bulk Upload" => self::$bulkUpload,
        "Finance" => self::$finance,
        "Package Tracking" => self::$packageTracking,
        "Package Sales" => self::$packageSales,
    ];

    if (!empty($exclude)) {
        $permissions = collect($permissions)->reject(function ($_, $key) use ($exclude) {
            return in_array($key, $exclude);
        })->toArray();
    }

    return $permissions;
}


    public static function userCan($pageRole, $permissions)
    {
        $permissions = json_decode($permissions);
        return in_array($pageRole, $permissions);
    }

    public static function user_can($page_role)
    {
        $actions1 = $_SESSION['actions'];
        $actions = json_decode($actions1);
        return in_array($page_role, $actions);
    }

    public static function is_assoc(array $array)
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }
}