<?php

namespace App\Helper;

use App\Entities\Account\Superuser;
use App\Entities\Utility\Permission;

class PermissionHelper
{
    const ACTIONS = [
        'manage', 'print', 'acc', 'create', 'show', 'edit', 'delete'
    ];

    const MODULES = [
        'MASTER' => [
            'company',
            'branch office',
            'warehouse',
            'product',
            'product category',
            'product type',
            'unit',
            'customer',
            'customer category',
            'customer type',
            'brand',
            'sub brand',
            'supplier',
            'ekspedisi'
        ],
        'PURCHASING' => [
            'purchase order',
            'receiving'
        ],
        'QUALITY CONTROL' => [
            'quality control utama',
            'quality control display'
        ],
        'INVENTORY' => [
            'stock',
            'mutation',
            'mutation display',
            'recondition',
            'stock adjusment',
            'product conversion'
        ],
        'SALE' => [
            'sales order',
            'delivery order',
            'do validate',
            'sale return',
            'buy back'
        ],
        'FINANCE' => [
            'cash/bank receipt',
            'cash/bank receipt (inv)',
            'cash/bank payment',
            'cash/bank payment (inv)',
            'marketplace receipt',
            'setting finance'
        ],
        'TRANSACTION REPORT' => [
            'purchase report',
            'sales report',
            'daily cash/bank recapitulation',
            'delivery progress',
            'all stock',
            'hpp report',
            'receiving report',
            'gudang utama',
            'stock valuation'
        ],
        'ACCOUNTING' => [
            'master coa',
            'journal',
            'daily cash/bank report',
        ],
        'ACCOUNTING REPORT' => [
            'general ledger',
            'profit loss',
            'cash flow',
            'balance sheet'
        ],
        'ACCOUNT' => [
            'superuser',
            // 'salesperson'
        ],
        'UTILITY' => [
            'settings'
        ],
        'DEVELOPER' => [
            'boilerplate',
            'telescope',
            'terminal',
            'gate'
        ]
    ];

    public static function countSuperuserWithoutRole()
    {
        return Superuser::doesntHave('roles')->count();
    }

    public static function isPermissionExists($permission, $guard = 'web')
    {
        $permission = Permission::where([
            'name' => $permission,
            'guard_name' => $guard
        ])->first();

        if ($permission) {
            return true;
        } else {
            return false;
        }
    }
}