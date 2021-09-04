<?php

namespace App\Repositories;

use App\Entities\Accounting\Coa;
use App\Entities\Master\BranchOffice;
use App\Entities\Master\BrandReference;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerType;
use App\Entities\Master\Product;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Unit;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Supplier;
use App\Entities\Master\Ekspedisi;
use App\Entities\Purchasing\PurchaseOrder;
use Illuminate\Support\Facades\Auth;

class MasterRepo
{
    public static function brand_references()
    {   
        return BrandReference::where('status', BrandReference::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function sub_brand_references()
    {   
        return SubBrandReference::where('status', SubBrandReference::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function products()
    {   
        return Product::where('status', Product::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function product_categories()
    {   
        return ProductCategory::where('status', ProductCategory::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function product_types()
    {   
        return ProductType::where('status', ProductType::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function customers()
    {   
        return Customer::where('status', Customer::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function customer_categories()
    {   
        return CustomerCategory::where('status', CustomerCategory::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function customer_types()
    {   
        return CustomerType::where('status', CustomerType::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function units()
    {   
        return Unit::where('status', Unit::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function warehouses()
    {   
        return Warehouse::where('status', Warehouse::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function warehouses_by_branch()
    {   
        if($type = Auth::guard('superuser')->user()->type) {
            if($type == Warehouse::TYPE['HEAD_OFFICE']) {
                return Warehouse::where('type', $type)->where('status', Warehouse::STATUS['ACTIVE'])->orderBy('name')->get();
            }

            $branch_id = Auth::guard('superuser')->user()->branch_office_id;
            return Warehouse::where('branch_office_id', $branch_id)->where('status', Warehouse::STATUS['ACTIVE'])->orderBy('name')->get();
        }
        
        return Warehouse::where('status', Warehouse::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function warehouses_by_category($category)
    {   
        if($type = Auth::guard('superuser')->user()->type) {
            if($type == Warehouse::TYPE['HEAD_OFFICE']) {
                return Warehouse::where('type', $type)
                        ->where('category', $category)
                        ->where('status', Warehouse::STATUS['ACTIVE'])
                        ->orderBy('name')->get();
            }

            $branch_id = Auth::guard('superuser')->user()->branch_office_id;
            return Warehouse::where('branch_office_id', $branch_id)
                        ->where('category', $category)
                        ->where('status', Warehouse::STATUS['ACTIVE'])
                        ->orderBy('name')->get();
        }
        
        return Warehouse::where('status', Warehouse::STATUS['ACTIVE'])->where('category', $category)->orderBy('name')->get();
    }
    

    public static function branch_offices()
    {
        return BranchOffice::where('status', BranchOffice::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function suppliers()
    {   
        return Supplier::where('status', Supplier::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function ekspedisis()
    {   
        return Ekspedisi::where('status', Ekspedisi::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function purchase_orders()
    {   
        return PurchaseOrder::where('status', PurchaseOrder::STATUS['ACC'])->orderBy('code')->get();
    }

    public static function coas_by_branch()
    {   
        $superuser = Auth::guard('superuser')->user();
        return Coa::where('type', $superuser->type)
                    ->where('branch_office_id', $superuser->branch_office_id)
                    ->where('status', Coa::STATUS['ACTIVE'])->get();
    }

    public static function coas_by_branch_and_group($group)
    {   
        $superuser = Auth::guard('superuser')->user();
        return Coa::where('type', $superuser->type)
                    ->where('branch_office_id', $superuser->branch_office_id)
                    ->where('group', $group)
                    ->where('status', Coa::STATUS['ACTIVE'])->get();
    }

}