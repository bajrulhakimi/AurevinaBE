<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin Aurevina',
            'email' => 'admin@aurevina.com',
            'phone' => '081234567890',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create customer user
        User::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '082345678901',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // Create categories
        $categories = [
            ['category_name' => 'Hijab Modern', 'slug' => 'hijab-modern'],
            ['category_name' => 'Hijab Syar\'i', 'slug' => 'hijab-syari'],
            ['category_name' => 'Pashmina', 'slug' => 'pashmina'],
            ['category_name' => 'Bergo', 'slug' => 'bergo'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Create products with variants
        $categories = Category::all();
        foreach ($categories as $category) {
            for ($i = 1; $i <= 3; $i++) {
                Product::create([
                    'category_id' => $category->id,
                    'product_name' => "{$category->category_name} Premium $i",
                    'slug' => Str::slug("{$category->category_name} Premium $i"),
                    'sku' => strtoupper(Str::slug($category->category_name)) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'description' => "Produk hijab premium dari kategori {$category->category_name}.",
                    'total_stock' => 100 + ($i * 10),
                    'base_price' => 75000 + ($i * 10000),
                    'weight' => 0.2,
                    'main_image' => null,
                    'status' => 'active',
                ]);
            }
        }

        $this->call(ProductVariantSeeder::class);
        $this->call(ContentSeeder::class);
    }
}
