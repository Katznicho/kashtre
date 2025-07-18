<?php

namespace App\Traits;

trait AccessTrait
{
    public static $admin = [
        "Dashboard" => [
            "View Dashboard", "View Dashboard Cards", "View Dashboard Charts", "View Dashboard Tables",
            // "View Only Assigned",
        ]
    ];

    //entities
    public static $entities = [
        // "Module" => ['Entities Management'],
        "Entities" => ['View Entities', 'Edit Entities', 'Add Entities', 'Delete Entities'],
    ];


    public static $departments = [
        // "Module" => ['Departments Management'],
        "Departments" => ['View Departments', 'Edit Departments', 'Add Departments', 'Delete Departments'],
    ];


 

    public static $staff = [
        // "Module" => ['Staff Management'],
        "Staff" => ['View Staff', 'Edit Staff', 'Add Staff', 'Delete Staff', 'Assign Roles'],
    ];

    public static $reports = [
        // "Module" => ['Reports Management'],
        "Reports" => ['View Report', 'Edit Report', 'Add Report', 'Delete Report'],

    ];



    // public static $logs = [
    //     "Module" => ['Logs Management'],
    //     "Logs" => ['View Logs', 'Edit Logs', 'Add Logs', 'Delete Logs'],
    // ];

    public static $logs = [
        // "Module" => ['Logs Management'],
        "Logs" => ['View Logs'],
    ];

    //roles
    public static $contractor = [
        // "Module" => ['Contractor Management'],
        "Contractor" => ["View Contractor", "Edit Contractor", "Add Contractor"],
    ];

    

    //products
    public static $products = [
        // "Module" => ['Products Management'],
        "Products" => ['View Products', 'Edit Products', 'Add Products', 'Delete Products'],
    ];

    //sales
    public static $sales = [
        // "Module" => ['Sales Management'],
        "Sales" => ['View Sales', 'Edit Sales', 'Add Sales', 'Delete Sales'],
    ];


    public static $clients = [
        // "Module" => ['Clients Management'],
        "Clients" => ['View Clients', 'Edit Clients', 'Add Clients', 'Delete Clients'],
    ];


    public static $queues = [
        // "Module" => ['Queues Management'],
        "Customers" => ['View Queues'],
    ];

    public static  $withdrawaal = [
        // "Module" => ['Withdrawals Management'],
        "Withdrawals" => ['View Withdrawals', 'Edit Withdrawals', 'Add Withdrawals', 'Delete Withdrawals'],
    ];

    public static $modules = [
        // "Module" => ['Modules Management'],
        "Modules" => ['View Modules', 'Edit Modules', 'Add Modules', 'Delete Modules'],
    ];

    public static  $stock = [
        // "Module" => ['Stock Management'],
        "Stock" => ['View Stock', 'Edit Stock', 'Add Stock', 'Delete Stock'],
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
        return  $result;
    }

    public static function getAllPermissions()
    {
        $roles = static::spreadArrayKeys(
            array_merge(
                static::$admin,

                static::$entities,

                static::$departments,

                static::$staff,
                
                static::$reports,
              
                
               
                static::$logs,

                static::$contractor,
             
                static::$products,

                static::$sales,

                static::$clients,

                static::$queues

                




            )
        );
        return $roles;
    }
    public static function getAccessControl()
    {

        $access = [
            "Dashboard" => self::$admin,

            "Entities" => self::$entities,

            "Departments" =>self::$departments,
           
            "Staff" => self::$staff,

            "Reports" =>  self::$reports,
      
            "Logs" => self::$logs,

            "Contractor" => self::$contractor,
           
            "Products" => self::$products,
            
            "Sales" => self::$sales,

            "Clients" => self::$clients,
            "Queues" => self::$queues,
            "Withdrawals" => self::$withdrawaal,
            "Modules" => self::$modules,
            "Stock" => self::$stock,

           


            // "Accounting" => static::$accounting
        ];
        return $access;
    }


    /**
     * Check if the user has specific role permission.
     *
     * @param datatype $pageRole description of page role
     * @param datatype $actions description of actions
     * @return boolean
     */
    public static function userCan($pageRole, $permissions)
    {
        $permissions =  json_decode($permissions);
        return in_array($pageRole, $permissions);
    }

    public static function user_can($page_role)
    {
        $actions1 = $_SESSION['actions'];
        $actions = json_decode($actions1);
        // print_r($actions);
        return in_array($page_role, $actions);
    }
    public static function is_assoc(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);
        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }
}
