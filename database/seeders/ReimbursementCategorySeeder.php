<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReimbursementCategory;

class ReimbursementCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Travel',
                'code' => 'TRAVEL',
                'description' => 'Travel expenses including fuel, taxi, bus, train, etc.',
                'max_amount_per_claim' => 5000,
                'max_amount_per_month' => 15000,
                'requires_receipt' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Medical',
                'code' => 'MEDICAL',
                'description' => 'Medical expenses including medicines, doctor fees, hospital bills, etc.',
                'max_amount_per_claim' => 10000,
                'max_amount_per_month' => 25000,
                'requires_receipt' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Internet',
                'code' => 'INTERNET',
                'description' => 'Internet and broadband expenses for work from home.',
                'max_amount_per_claim' => 2000,
                'max_amount_per_month' => 2000,
                'requires_receipt' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Training',
                'code' => 'TRAINING',
                'description' => 'Training and certification expenses.',
                'max_amount_per_claim' => 15000,
                'max_amount_per_month' => 30000,
                'requires_receipt' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Books',
                'code' => 'BOOKS',
                'description' => 'Books and reference materials for work.',
                'max_amount_per_claim' => 2000,
                'max_amount_per_month' => 5000,
                'requires_receipt' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Equipment',
                'code' => 'EQUIPMENT',
                'description' => 'Office equipment and supplies.',
                'max_amount_per_claim' => 10000,
                'max_amount_per_month' => 20000,
                'requires_receipt' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Meals',
                'code' => 'MEALS',
                'description' => 'Business meal expenses.',
                'max_amount_per_claim' => 500,
                'max_amount_per_month' => 3000,
                'requires_receipt' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ReimbursementCategory::create($category);
        }
    }
}
