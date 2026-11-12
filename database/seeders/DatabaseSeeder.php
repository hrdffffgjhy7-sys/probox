<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Salesman;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view' => ['Dashboard', 'View dashboard'],
            'suppliers.view' => ['Suppliers', 'View suppliers'],
            'suppliers.manage' => ['Suppliers', 'Create and edit suppliers'],
            'suppliers.delete' => ['Suppliers', 'Archive, restore or delete suppliers'],
            'agencies.manage' => ['Agencies', 'Manage agencies'],
            'salesmen.manage' => ['Salesmen', 'Manage salesmen'],
            'purchases.view' => ['Purchases', 'View purchase invoices'],
            'purchases.manage' => ['Purchases', 'Create and edit purchases'],
            'payments.view' => ['Payments', 'View payments'],
            'payments.manage' => ['Payments', 'Record and edit payments'],
            'ledger.view' => ['Ledger', 'View supplier khata and statements'],
            'payables.view' => ['Finance', 'View payables'],
            'receivables.view' => ['Finance', 'View receivables'],
            'expenses.view' => ['Expenses', 'View expenses'],
            'expenses.manage' => ['Expenses', 'Record expenses'],
            'reports.view' => ['Reports', 'View and export reports'],
            'products.manage' => ['Products', 'Manage products'],
            'backups.manage' => ['System', 'Create and restore backups'],
            'users.manage' => ['System', 'Manage users and roles'],
            'settings.manage' => ['System', 'Manage settings'],
            'audit.view' => ['System', 'View audit trail'],
        ];

        foreach ($permissions as $name => [$group, $label]) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['group' => $group, 'display_name' => $label]
            );
        }

        $admin = Role::updateOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'Full access to every module.']
        );
        $admin->permissions()->sync(Permission::pluck('id'));

        $manager = Role::updateOrCreate(
            ['name' => 'manager'],
            ['display_name' => 'Manager', 'description' => 'Day to day operations without system administration.']
        );
        $manager->permissions()->sync(Permission::whereNotIn('name', [
            'users.manage', 'settings.manage', 'backups.manage', 'audit.view',
        ])->pluck('id'));

        $staff = Role::updateOrCreate(
            ['name' => 'staff'],
            ['display_name' => 'Staff', 'description' => 'Read-only access to accounts and reports.']
        );
        $staff->permissions()->sync(Permission::whereIn('name', [
            'dashboard.view', 'suppliers.view', 'purchases.view', 'payments.view',
            'ledger.view', 'payables.view', 'receivables.view', 'reports.view',
        ])->pluck('id'));

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'role_id' => $admin->id,
                'is_active' => true,
                'password' => Hash::make('admin123'),
                'must_change_password' => false,
            ]
        );

        User::updateOrCreate(
            ['username' => 'staff'],
            [
                'name' => 'Store Staff',
                'email' => 'staff@example.com',
                'role' => 'staff',
                'role_id' => $staff->id,
                'is_active' => true,
                'password' => Hash::make('staff123'),
                'must_change_password' => false,
            ]
        );

        $settings = [
            'business_name' => 'Kiryana Khata Pro',
            'business_phone' => '0300 1234567',
            'business_address' => 'Main Bazaar, Karachi',
            'currency_symbol' => 'Rs.',
            'default_locale' => 'en',
            'session_timeout' => '30',
            'backup_retention' => '30',
            'low_stock_threshold' => '5',
        ];
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if (Agency::count() === 0) {
            $agencies = collect([
                ['name' => 'Al-Madina Traders', 'phone' => '0300 1112223', 'address' => 'Jodia Bazaar'],
                ['name' => 'Shaheen Distributors', 'phone' => '0321 4455667', 'address' => 'Lea Market'],
                ['name' => 'Bismillah Suppliers', 'phone' => '0333 7788990', 'address' => 'Empress Market'],
            ])->map(fn ($row) => Agency::create($row + ['is_active' => true]));

            $salesmen = collect([
                ['name' => 'Imran Ali', 'phone' => '0301 1231234', 'agency_id' => $agencies[0]->id],
                ['name' => 'Farhan Khan', 'phone' => '0345 4564567', 'agency_id' => $agencies[1]->id],
                ['name' => 'Bilal Ahmed', 'phone' => '0312 7897890', 'agency_id' => $agencies[2]->id],
            ])->map(fn ($row) => Salesman::create($row + ['is_active' => true]));

            $ledger = app(LedgerService::class);
            $suppliers = [
                ['name' => 'Haji Store', 'agency_id' => $agencies[0]->id, 'salesman_id' => $salesmen[0]->id, 'phone' => '0300 5551111', 'opening_balance' => 15000, 'opening_balance_type' => 'payable'],
                ['name' => 'Noor General Store', 'agency_id' => $agencies[1]->id, 'salesman_id' => $salesmen[1]->id, 'phone' => '0300 5552222', 'opening_balance' => 0, 'opening_balance_type' => 'payable'],
                ['name' => 'Rashid Kiryana', 'agency_id' => $agencies[2]->id, 'salesman_id' => $salesmen[2]->id, 'phone' => '0300 5553333', 'opening_balance' => 4500, 'opening_balance_type' => 'receivable'],
            ];
            foreach ($suppliers as $row) {
                $supplier = Supplier::create($row + [
                    'current_balance' => 0,
                    'is_active' => true,
                    'created_by' => 1,
                ]);
                $ledger->openingEntry($supplier);
            }

            foreach ([
                ['name' => 'Basmati Rice 5kg', 'unit' => 'bag', 'purchase_rate' => 1850, 'sale_rate' => 2000, 'stock_qty' => 24, 'low_stock_alert' => 5, 'is_active' => true],
                ['name' => 'Cooking Oil 1L', 'unit' => 'pcs', 'purchase_rate' => 540, 'sale_rate' => 590, 'stock_qty' => 60, 'low_stock_alert' => 10, 'is_active' => true],
                ['name' => 'Sugar 1kg', 'unit' => 'kg', 'purchase_rate' => 145, 'sale_rate' => 160, 'stock_qty' => 120, 'low_stock_alert' => 20, 'is_active' => true],
                ['name' => 'Tea 500g', 'unit' => 'pack', 'purchase_rate' => 980, 'sale_rate' => 1050, 'stock_qty' => 15, 'low_stock_alert' => 5, 'is_active' => true],
            ] as $row) {
                Product::create($row);
            }
        }
    }
}
