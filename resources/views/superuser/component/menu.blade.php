<ul class="nav-main">
  <li>
    <a href="{{ route('superuser.index') }}" class="{{ (Route::currentRouteName() == 'superuser.index') ? 'active' : '' }}">
      <i class="si si-home"></i>
      <span class="sidebar-mini-hide">Dashboard</span>
    </a>
  </li>

  @if($superuser->hasAnyDirectPermission(['company-manage','branch office-manage','warehouse-manage','product-manage','product category-manage','product type-manage','unit-manage','customer-manage','customer category-manage','customer type-manage','brand-manage','sub brand-manage','supplier-manage','ekspedisi-manage']))
  <li class="{{ is_open_route('superuser/master') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-folder-alt"></i>
      <span class="sidebar-mini-hide">
        Master
      </span>
    </a>
    <ul>
      @if($superuser->can('company-manage'))
      <li>
        <a href="{{ route('superuser.master.company.show') }}" class="{{ is_active_route('superuser.master.company.show') }}">
          Company
        </a>
      </li>
      @endif
      @if($superuser->can('branch office-manage'))
      <li>
        <a href="{{ route('superuser.master.branch_office.index') }}" class="{{ is_active_route('superuser.master.branch_office.index') }}">
          Branch Office
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.store.index') }}" class="{{ is_active_route('superuser.master.store.index') }}">
          Store
        </a>
      </li>
      @endif
      @if($superuser->can('warehouse-manage'))
      <li>
        <a href="{{ route('superuser.master.warehouse.index') }}" class="{{ is_active_route('superuser.master.warehouse.index') }}">
          Warehouse
        </a>
      </li>
      @endif
      @if($superuser->can('product-manage'))
      <li>
        <a href="{{ route('superuser.master.product.index') }}" class="{{ is_active_route('superuser.master.product.index') }}">
          Product
        </a>
      </li>
      @endif
      @if($superuser->can('product category-manage'))
      <li>
        <a href="{{ route('superuser.master.product_category.index') }}" class="{{ is_active_route('superuser.master.product_category.index') }}">
          Product Category
        </a>
      </li>
      @endif
      @if($superuser->can('product type-manage'))
      <li>
        <a href="{{ route('superuser.master.product_type.index') }}" class="{{ is_active_route('superuser.master.product_type.index') }}">
          Sub Category
        </a>
      </li>
      @endif
      @if($superuser->can('unit-manage'))
      <li>
        <a href="{{ route('superuser.master.unit.index') }}" class="{{ is_active_route('superuser.master.unit.index') }}">
          Unit
        </a>
      </li>
      @endif
      @if($superuser->can('customer-manage'))
      <li>
        <a href="{{ route('superuser.master.customer.index') }}" class="{{ is_active_route('superuser.master.customer.index') }}">
          Customer
        </a>
      </li>
      @endif
      @if($superuser->can('customer category-manage'))
      <li>
        <a href="{{ route('superuser.master.customer_category.index') }}" class="{{ is_active_route('superuser.master.customer_category.index') }}">
          Customer Category
        </a>
      </li>
      @endif
      @if($superuser->can('customer type-manage'))
      <li>
        <a href="{{ route('superuser.master.customer_type.index') }}" class="{{ is_active_route('superuser.master.customer_type.index') }}">
          Customer Type
        </a>
      </li>
      @endif
      @if($superuser->can('brand-manage'))
      <li>
        <a href="{{ route('superuser.master.brand_reference.index') }}" class="{{ is_active_route('superuser.master.brand_reference.index') }}">
          Brand
        </a>
      </li>
      @endif
      @if($superuser->can('sub brand-manage'))
      <li>
        <a href="{{ route('superuser.master.sub_brand_reference.index') }}" class="{{ is_active_route('superuser.master.sub_brand_reference.index') }}">
          Sub Brand
        </a>
      </li>
      @endif
      @if($superuser->can('supplier-manage'))
      <li>
        <a href="{{ route('superuser.master.supplier.index') }}" class="{{ is_active_route('superuser.master.supplier.index') }}">
          Supplier
        </a>
      </li>
      @endif
      @if($superuser->can('ekspedisi-manage'))
      <li>
        <a href="{{ route('superuser.master.ekspedisi.index') }}" class="{{ is_active_route('superuser.master.ekspedisi.index') }}">
          Ekspedisi
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['purchase order-manage', 'receiving-manage']))
  <li class="{{ is_open_route('superuser/purchasing') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-basket"></i>
      <span class="sidebar-mini-hide">
        Purchasing
      </span>
    </a>
    <ul>
      @if($superuser->can('purchase order-manage'))
      <li>
        <a href="{{ route('superuser.purchasing.purchase_order.index') }}" class="{{ is_active_route('superuser.purchasing.purchase_order.index') }}">
          Purchase Order (PPB)
        </a>
      </li>
      @endif
      @if($superuser->can('receiving-manage'))
      <li>
        <a href="{{ route('superuser.purchasing.receiving.index') }}" class="{{ is_active_route('superuser.purchasing.receiving.index') }}">
          Receiving
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['quality control utama-manage', 'quality control display-manage']))
  <li class="{{ is_open_route('superuser/quality_control') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-hourglass"></i>
      <span class="sidebar-mini-hide">
        Quality Control
      </span>
    </a>
    <ul>
      @if($superuser->can('quality control utama-manage'))
      <li>
        <a href="{{ route('superuser.quality_control.quality_control_1.index') }}" class="{{ is_active_route('superuser.quality_control.quality_control_1.index') }}">
          Quality Control Utama
        </a>
      </li>
      @endif
      @if($superuser->can('quality control display-manage'))
      <li>
        <a href="{{ route('superuser.quality_control.quality_control_2.index') }}" class="{{ is_active_route('superuser.quality_control.quality_control_2.index') }}">
          Quality Control Display
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['stock-manage', 'mutation-manage','recondition-manage','stock adjusment-manage', 'mutation display-manage']))
  <li class="{{ is_open_route('superuser/inventory') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-cubes"></i>
      <span class="sidebar-mini-hide">
        Inventory
      </span>
    </a>
    <ul>
      @if($superuser->can('stock-manage'))
      <li>
        <a href="{{ route('superuser.inventory.stock.index') }}" class="{{ is_active_route('superuser.inventory.stock.index') }}">
          Stock
        </a>
      </li>
      @endif
      @if($superuser->can('mutation-manage'))
      <li>
        <a href="{{ route('superuser.inventory.mutation.index') }}" class="{{ is_active_route('superuser.inventory.mutation.index') }}">
          Mutation
        </a>
      </li>
      @endif
      @if($superuser->can('mutation display-manage'))
      <li>
        <a href="{{ route('superuser.inventory.mutation_display.index') }}" class="{{ is_active_route('superuser.inventory.mutation_display.index') }}">
          Mutation Display
        </a>
      </li>
      @endif
      @if($superuser->can('recondition-manage'))
      <li>
        <a href="{{ route('superuser.inventory.recondition.index') }}" class="{{ is_active_route('superuser.inventory.recondition.index') }}">
          Recondition
        </a>
      </li>
      @endif
      @if($superuser->can('stock adjusment-manage'))
      <li>
        <a href="{{ route('superuser.inventory.stock_adjusment.index') }}" class="{{ is_active_route('superuser.inventory.stock_adjusment.index') }}">
          Stock Adjusment
        </a>
      </li>
      @endif
      @if($superuser->can('product conversion-manage'))
      <li>
        <a href="{{ route('superuser.inventory.product_conversion.index') }}" class="{{ is_active_route('superuser.inventory.product_conversion.index') }}">
          Product Conversion
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['sales order-manage', 'delivery order-manage','do validate-manage','sale return-manage','buy back-manage']))
  <li class="{{ is_open_route('superuser/sale') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-database"></i>
      <span class="sidebar-mini-hide">
        Sale
      </span>
    </a>
    <ul>
      @if($superuser->can('sales order-manage'))
      <li>
        <a href="{{ route('superuser.sale.sales_order.index') }}" class="{{ is_active_route('superuser.sale.sales_order.index') }}">
          Sales Order
        </a>
      </li>
      @endif
      @if($superuser->can('delivery order-manage'))
      <li>
        <a href="{{ route('superuser.sale.delivery_order.index') }}" class="{{ is_active_route('superuser.sale.delivery_order.index') }}">
          Delivery Order
        </a>
      </li>
      @endif
      @if($superuser->can('do validate-manage'))
      <li>
        <a href="{{ route('superuser.sale.do_validate.index') }}" class="{{ is_active_route('superuser.sale.do_validate.index') }}">
          DO Validate
        </a>
      </li>
      @endif
      @if($superuser->can('sale return-manage'))
      <li>
        <a href="{{ route('superuser.sale.sale_return.index') }}" class="{{ is_active_route('superuser.sale.sale_return.index') }}">
          Sale Return
        </a>
      </li>
      @endif
      @if($superuser->can('buy back-manage'))
      <li>
        <a href="{{ route('superuser.sale.buy_back.index') }}" class="{{ is_active_route('superuser.sale.buy_back.index') }}">
          Buy Back
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['cash/bank receipt-manage', 'cash/bank receipt (inv)-manage', 'cash/bank payment-manage', 'cash/bank payment (inv)-manage', 'marketplace receipt-manage', 'setting finance-manage', 'journal entry-manage', 'journal setting-manage']))
  <li class="{{ is_open_route('superuser/finance') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-money"></i>
      <span class="sidebar-mini-hide">
        Finance
      </span>
    </a>
    <ul>
      @if($superuser->can('cash/bank receipt-manage'))
      <li>
        <a href="{{ route('superuser.finance.receipt.index') }}" class="{{ is_active_route('superuser.finance.receipt.index') }}">
          Cash/Bank Receipt
        </a>
      </li>
      @endif
      @if($superuser->can('cash/bank receipt (inv)-manage'))
      <li>
        <a href="{{ route('superuser.finance.receipt_invoice.index') }}" class="{{ is_active_route('superuser.finance.receipt_invoice.index') }}">
          Cash/Bank Receipt (Inv)
        </a>
      </li>
      @endif
      @if($superuser->can('cash/bank payment-manage'))
      <li>
        <a href="{{ route('superuser.finance.payment.index') }}" class="{{ is_active_route('superuser.finance.payment.index') }}">
          Cash/Bank Payment
        </a>
      </li>
      @endif
      @if($superuser->can('cash/bank payment (inv)-manage'))
      <li>
        <a href="{{ route('superuser.finance.payment_invoice.index') }}" class="{{ is_active_route('superuser.finance.payment_invoice.index') }}">
          Cash/Bank Payment (Inv)
        </a>
      </li>
      @endif
      @if($superuser->can('marketplace receipt-manage'))
      <li>
        <a href="{{ route('superuser.finance.marketplace_receipt.index') }}" class="{{ is_active_route('superuser.finance.marketplace_receipt.index') }}">
          Marketplace Receipt
        </a>
      </li>
      @endif
      @if($superuser->can('setting finance-manage'))
      <li>
        <a href="{{ route('superuser.finance.setting_finance.index') }}" class="{{ is_active_route('superuser.finance.setting_finance.index') }}">
          Setting Finance
        </a>
      </li>
      @endif
      @if($superuser->can('journal entry-manage'))
      <li>
        <a href="{{ route('superuser.finance.journal_entry.index') }}" class="{{ is_active_route('superuser.finance.journal_entry.index') }}">
          Journal Entry
        </a>
      </li>
      @endif
      @if($superuser->can('journal setting-manage'))
      <li>
        <a href="{{ route('superuser.finance.journal_setting.index') }}" class="{{ is_active_route('superuser.finance.journal_setting.index') }}">
          Journal Setting
        </a>
      </li>
      @endif
      @role('Developer', 'superuser')
      <li>
        <a href="{{ route('superuser.finance.secret_setting.index') }}" class="{{ is_active_route('superuser.finance.secret_setting.index') }}">
          SECRET SETTING (WARNING)
        </a>
      </li>
      @endrole
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['purchase report-manage', 'sales report-manage', 'daily cash/bank recapitulation-manage', 'delivery progress-manage', 'all stock-manage', 'hpp report-manage', 'receiving report-manage', 'gudang utama-manage']))
  <li class="{{ is_open_route('superuser/transaction_report') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-file-text"></i>
      <span class="sidebar-mini-hide">
        Transaction Report
      </span>
    </a>
    <ul>
      @if($superuser->can('purchase report-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.purchase_report.index') }}" class="{{ is_active_route('superuser.transaction_report.purchase_report.index') }}">
          Purchase Report
        </a>
      </li>
      @endif
      @if($superuser->can('sales report-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.sales_report.index') }}" class="{{ is_active_route('superuser.transaction_report.sales_report.index') }}">
          Sales Report
        </a>
      </li>
      @endif
      @if($superuser->can('daily cash/bank recapitulation-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.daily_recapitulation.index') }}" class="{{ is_active_route('superuser.transaction_report.daily_recapitulation.index') }}">
          Daily Cash/Bank Recapitulation
        </a>
      </li>
      @endif
      @if($superuser->can('delivery progress-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.delivery_progress.index') }}" class="{{ is_active_route('superuser.transaction_report.delivery_progress.index') }}">
          Delivery Progress
        </a>
      </li>
      @endif
      @if($superuser->can('all stock-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.all_stock.index') }}" class="{{ is_active_route('superuser.transaction_report.all_stock.index') }}">
          All Stock
        </a>
      </li>
      @endif
      @if($superuser->can('hpp report-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.hpp_report.index') }}" class="{{ is_active_route('superuser.transaction_report.hpp_report.index') }}">
          HPP Report
        </a>
      </li>
      @endif
      @if($superuser->can('receiving report-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.receiving_report.index') }}" class="{{ is_active_route('superuser.transaction_report.receiving_report.index') }}">
          Receiving Report
        </a>
      </li>
      @endif
      @if($superuser->can('gudang utama-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.gudang_utama_report.index') }}" class="{{ is_active_route('superuser.transaction_report.gudang_utama_report.index') }}">
          Gudang Utama
        </a>
      </li>
      @endif
      @if($superuser->can('recondition-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.recondition_report.index') }}" class="{{ is_active_route('superuser.transaction_report.recondition_report.index') }}">
          Recondition
        </a>
      </li>
      @endif
      @if($superuser->can('stock valuation-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.stock_valuation.index') }}" class="{{ is_active_route('superuser.transaction_report.stock_valuation.index') }}">
          Stock Valuation
        </a>
      </li>
      @endif
      {{-- @if($superuser->can('conversion-manage')) --}}
      <li>
        <a href="{{ route('superuser.transaction_report.conversion_report.index') }}" class="{{ is_active_route('superuser.transaction_report.conversion_report.index') }}">
          Product Conversion
        </a>
      </li>
      {{-- @endif --}}
      @if($superuser->can('sales order-manage'))
      <li>
        <a href="{{ route('superuser.transaction_report.order_detail_report.index') }}" class="{{ is_active_route('superuser.transaction_report.order_detail_report.index') }}">
          Order Detail per SKU
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['master coa-manage', 'journal-manage', 'daily cash/bank report-manage']))
  <li class="{{ is_open_route('superuser/accounting') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-calculator"></i>
      <span class="sidebar-mini-hide">
        Accounting
      </span>
    </a>
    <ul>
      @if($superuser->can('master coa-manage'))
      <li>
        <a href="{{ route('superuser.accounting.coa.index') }}" class="{{ is_active_route('superuser.accounting.coa.index') }}">
          Master COA
        </a>
      </li>
      @endif
      @if($superuser->can('journal-manage'))
      <li>
        <a href="{{ route('superuser.accounting.journal.index') }}" class="{{ is_active_route('superuser.accounting.journal.index') }}">
          Journal
        </a>
      </li>
      @endif
      @if($superuser->can('daily cash/bank report-manage'))
      <li>
        <a href="{{ route('superuser.accounting.daily_report.index') }}" class="{{ is_active_route('superuser.accounting.daily_report.index') }}">
          Daily Cash/Bank Report
        </a>
      </li>
      @endif
      {{-- @if($superuser->can('setting profit loss-manage'))
      <li>
        <a href="{{ route('superuser.accounting.setting_profit_loss.index') }}" class="{{ is_active_route('superuser.accounting.setting_profit_loss.index') }}">
          Setting Profit Loss
        </a>
      </li>
      @endif --}}
    </ul>
  </li>
  @endif

  @if($superuser->hasAnyDirectPermission(['general ledger-manage', 'profit loss-manage', 'cash flow-manage', 'balance sheet-manage']))
  <li class="{{ is_open_route('superuser/report') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-file-text"></i>
      <span class="sidebar-mini-hide">
        Accounting Report
      </span>
    </a>
    <ul>
      @if($superuser->can('general ledger-manage'))
      <li>
        <a href="{{ route('superuser.report.general_ledger.index') }}" class="{{ is_active_route('superuser.report.general_ledger.index') }}">
          General Ledger
        </a>
      </li>
      @endif
      @if($superuser->can('profit loss-manage'))
      <li>
        <a href="{{ route('superuser.report.profit_loss_report.index') }}" class="{{ is_active_route('superuser.report.profit_loss_report.index') }}">
          Profit Loss
        </a>
      </li>
      @endif
      @if($superuser->can('cash flow-manage'))
      <li>
        <a href="{{ route('superuser.report.cash_flow_report.index') }}" class="{{ is_active_route('superuser.report.cash_flow_report.index') }}">
          Cash Flow
        </a>
      </li>
      @endif
      @if($superuser->can('balance sheet-manage'))
      <li>
        <a href="{{ route('superuser.report.balance_sheet.index') }}" class="{{ is_active_route('superuser.report.balance_sheet.index') }}">
          Balance Sheet
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @if($superuser->canAny(['superuser-manage']))
  <li class="{{ is_open_route('superuser/account') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-users"></i>
      <span class="sidebar-mini-hide">
        Account
      </span>
    </a>
    <ul>
      @if($superuser->can('superuser-manage'))
      <li>
        <a href="{{ route('superuser.account.superuser.index') }}" class="{{ is_active_route('superuser.account.superuser.index') }}">
          Superuser
        </a>
      </li>
      @endif
      {{-- <li>
        <a href="{{ route('superuser.account.user.index') }}" class="{{ is_active_route('superuser.account.user.index') }}">
          User
        </a>
      </li> --}}
      {{-- @if($superuser->can('salesperson-manage'))
      <li>
        <a href="{{ route('superuser.account.sales_person.index') }}" class="{{ is_active_route('superuser.account.sales_person.index') }}">
          Sales Person
        </a>
      </li>
      @endif --}}
    </ul>
  </li>
  @endif

  @role('Developer|SuperAdmin', 'superuser')
  <li class="{{ is_open_route('superuser/utility') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-wrench"></i>
      <span class="sidebar-mini-hide">
        Utility
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.utility.settings.index') }}" class="{{ is_active_route('superuser.utility.settings.index') }}">
          Settings
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.utility.indonesian_teritory') }}" class="{{ is_active_route('superuser.utility.indonesian_teritory') }}">
          Indonesian Teritory
        </a>
      </li>
    </ul>
  </li>
  @endrole

  @role('Developer|SuperAdmin', 'superuser')
  <li>
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-shield"></i>
      <span class="sidebar-mini-hide">
        Developer
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.boilerplate.index') }}" class="{{ is_active_route('superuser.boilerplate.index') }}">
          Boilerplate
        </a>
      </li>
      <li>
        <a href="{{ url('superuser/telescope') }}" target="_blank">
          Telescope
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.terminal') }}" class="{{ is_active_route('superuser.terminal') }}">
          Terminal
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.gate.index') }}" class="{{ is_active_route('superuser.gate.index') }}">
          Gate (Authorization)
        </a>
      </li>
    </ul>
  </li>
  @endrole

  {{-- <li class="{{ is_open_route('superuser/account') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-users"></i>
      <span class="sidebar-mini-hide">
        Account
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.account.superuser.index') }}" class="{{ is_active_route('superuser.account.superuser.index') }}">
          Superuser
        </a>
      </li>
    </ul>
  </li> --}}

</ul>