<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds for DEMO purposes.
     * Includes Admin, Kasir, products, and realistic transaction history.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Demo Seeding...');

        // Create Users
        $this->command->info('👤 Creating users...');
        $admin = $this->createAdmin();
        $kasir1 = $this->createKasir('Kasir Pagi', 'kasir1@toko.com');
        $kasir2 = $this->createKasir('Kasir Siang', 'kasir2@toko.com');
        $kasir3 = $this->createKasir('Kasir Sore', 'kasir3@toko.com');

        $this->command->info("✅ Created: {$admin->name} ({$admin->email})");
        $this->command->info("✅ Created: {$kasir1->name} ({$kasir1->email})");
        $this->command->info("✅ Created: {$kasir2->name} ({$kasir2->email})");
        $this->command->info("✅ Created: {$kasir3->name} ({$kasir3->email})");

        // Create Products
        $this->command->info('📦 Creating products...');
        $products = $this->createProducts();
        $this->command->info("✅ Created {$products->count()} products");

        // Create Transaction History (Last 7 days)
        $this->command->info('💰 Creating transactions...');
        $kasirs = [$kasir1, $kasir2, $kasir3];
        $totalTransactions = 0;

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo);
            $transactionsPerDay = $daysAgo === 0 ? fake()->numberBetween(8, 15) : fake()->numberBetween(5, 12);

            for ($i = 0; $i < $transactionsPerDay; $i++) {
                $this->createTransaction($products, $kasirs, $date);
                $totalTransactions++;
            }
        }

        $this->command->info("✅ Created {$totalTransactions} transactions with items");

        // Create Stock Adjustments
        $this->command->info('📊 Creating stock adjustments...');
        $this->createStockAdjustments($products, $admin, $kasir1);

        // Create Low Stock Products (for testing notifications)
        $this->command->info('⚠️ Creating low stock products...');
        $this->createLowStockProducts();
        $this->command->info('✅ Created 5 low stock products');

        $this->command->info('🎉 Demo seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info("   - Users: " . User::count());
        $this->command->info("   - Products: " . Product::count());
        $this->command->info("   - Transactions: " . Transaction::count());
        $this->command->info("   - Transaction Items: " . TransactionItem::count());
        $this->command->info("   - Stock Adjustments: " . StockAdjustment::count());
    }

    protected function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    protected function createKasir(string $name, string $email): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);
    }

    protected function createProducts(): \Illuminate\Support\Collection
    {
        $productData = [
            // Makanan Instan
            ['name' => 'Indomie Goreng', 'purchase_price' => 2500, 'price' => 3500, 'stock' => 100, 'barcode' => '8991001100028'],
            ['name' => 'Indomie Kuah Soto', 'purchase_price' => 2300, 'price' => 3200, 'stock' => 80, 'barcode' => '8991001100035'],
            ['name' => 'Mie Sedaap Goreng', 'purchase_price' => 2400, 'price' => 3400, 'stock' => 75, 'barcode' => '8998866200017'],
            ['name' => 'Pop Mie Ayam', 'purchase_price' => 4000, 'price' => 5500, 'stock' => 50, 'barcode' => '8991002101014'],
            ['name' => 'Biskuit Roma Kelapa', 'purchase_price' => 3500, 'price' => 5000, 'stock' => 60, 'barcode' => '8991001100042'],

            // Minuman
            ['name' => 'Aqua 600ml', 'purchase_price' => 2500, 'price' => 4000, 'stock' => 200, 'barcode' => '8885049103005'],
            ['name' => 'Teh Botol Sosro 450ml', 'purchase_price' => 3500, 'price' => 5000, 'stock' => 150, 'barcode' => '8992761100018'],
            ['name' => 'Teh Pucuk Harum 500ml', 'purchase_price' => 3200, 'price' => 4500, 'stock' => 120, 'barcode' => '8996001600146'],
            ['name' => 'Susu Ultra 250ml', 'purchase_price' => 4500, 'price' => 6500, 'stock' => 90, 'barcode' => '8998009010019'],
            ['name' => 'Susu Bear Brand 189ml', 'purchase_price' => 8000, 'price' => 11000, 'stock' => 48, 'barcode' => '8992696412125'],
            ['name' => 'Kopi Kapal Api 165g', 'purchase_price' => 8500, 'price' => 12000, 'stock' => 40, 'barcode' => '8992761100063'],

            // Sembako
            ['name' => 'Beras Premium 5kg', 'purchase_price' => 60000, 'price' => 75000, 'stock' => 25, 'barcode' => '8991001200015'],
            ['name' => 'Minyak Goreng 1L', 'purchase_price' => 14000, 'price' => 18000, 'stock' => 40, 'barcode' => '8992702025112'],
            ['name' => 'Gula Pasir 1kg', 'purchase_price' => 12000, 'price' => 15000, 'stock' => 50, 'barcode' => '8991001300018'],
            ['name' => 'Telur Ayam 1kg', 'purchase_price' => 24000, 'price' => 30000, 'stock' => 20, 'barcode' => '2000000000017'],
            ['name' => 'Tepung Terigu 1kg', 'purchase_price' => 8000, 'price' => 12000, 'stock' => 35, 'barcode' => '8991001300025'],

            // Snack
            ['name' => 'Chitato Original 68g', 'purchase_price' => 8000, 'price' => 12000, 'stock' => 60, 'barcode' => '8996001302187'],
            ['name' => 'Oreo Original 137g', 'purchase_price' => 8500, 'price' => 12000, 'stock' => 45, 'barcode' => '8992760221128'],
            ['name' => 'Roti Tawar Sari Roti', 'purchase_price' => 11000, 'price' => 15000, 'stock' => 25, 'barcode' => '8992907412018'],
            ['name' => 'Ceres Coklat 200g', 'purchase_price' => 7500, 'price' => 11000, 'stock' => 50, 'barcode' => '8992761100070'],

            // Kebersihan
            ['name' => 'Sabun Lifebuoy 80g', 'purchase_price' => 3000, 'price' => 4500, 'stock' => 100, 'barcode' => '8999999027315'],
            ['name' => 'Shampoo Sunsilk 170ml', 'purchase_price' => 16000, 'price' => 22000, 'stock' => 40, 'barcode' => '8999999528515'],
            ['name' => 'Pasta Gigi Pepsodent 190g', 'purchase_price' => 10000, 'price' => 14000, 'stock' => 60, 'barcode' => '8999999749019'],
            ['name' => 'Sunlight 755ml', 'purchase_price' => 12000, 'price' => 17000, 'stock' => 40, 'barcode' => '8999999526216'],
            ['name' => 'Rinso Cair 900ml', 'purchase_price' => 18000, 'price' => 25000, 'stock' => 30, 'barcode' => '8999999526223'],

            // Low stock items (for testing notifications)
            ['name' => 'Garam Dapur 1kg', 'purchase_price' => 8000, 'price' => 12000, 'stock' => 3, 'barcode' => '8991001300032'],
            ['name' => 'Kecap ABC 625ml', 'purchase_price' => 15000, 'price' => 20000, 'stock' => 2, 'barcode' => '8992761100087'],
            ['name' => 'Sarden ABC Kaleng', 'purchase_price' => 12000, 'price' => 16000, 'stock' => 5, 'barcode' => '8992761100094'],
            ['name' => 'Teh Celup Sariwangi', 'purchase_price' => 5000, 'price' => 8000, 'stock' => 4, 'barcode' => '8992761100100'],
            ['name' => 'Susu Kental Manis', 'purchase_price' => 9000, 'price' => 13000, 'stock' => 1, 'barcode' => '8992761100117'],
        ];

        $products = collect();
        foreach ($productData as $data) {
            $products->push(Product::create($data));
        }

        return $products;
    }

    protected function createTransaction($products, $kasirs, $date): void
    {
        $kasir = $kasirs[array_rand($kasirs)];
        
        // Create transaction with random date/time
        $transactionDate = Carbon::parse($date)->addHours(rand(8, 21))->addMinutes(rand(0, 59));
        
        // Generate 1-4 items per transaction
        $numItems = rand(1, 4);
        $selectedProducts = $products->random(min($numItems, $products->count()));
        
        $total = 0;
        $items = [];

        foreach ($selectedProducts as $product) {
            $quantity = rand(1, 5);
            $subtotal = $quantity * $product->price;
            $total += $subtotal;

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
                'purchase_price' => $product->purchase_price,
                'subtotal' => $subtotal,
            ];
        }

        // Calculate payment (round up to nearest 1000 or exact)
        $cash = (int) (ceil($total / 1000) * 1000) + rand(0, 2) * 1000;
        $change = $cash - $total;

        // Create transaction
        $transaction = Transaction::create([
            'invoice_code' => 'INV-' . $transactionDate->format('Ymd') . '-' . str_pad(Transaction::whereDate('created_at', $transactionDate)->count() + 1, 4, '0', STR_PAD_LEFT),
            'user_id' => $kasir->id,
            'total' => $total,
            'cash' => $cash,
            'change' => $change,
            'created_at' => $transactionDate,
            'updated_at' => $transactionDate,
        ]);

        // Create transaction items
        foreach ($items as $item) {
            $transaction->items()->create($item);
        }

        // Update product stock
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->stock >= $item['quantity']) {
                $product->decrement('stock', $item['quantity']);
            }
        }
    }

    protected function createStockAdjustments($products, $admin, $kasir): void
    {
        // Restock adjustment (type: in)
        StockAdjustment::create([
            'product_id' => $products->random()->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 50,
            'notes' => 'Restock mingguan dari supplier',
        ]);

        // Stock correction (type: out - produk rusak)
        StockAdjustment::create([
            'product_id' => $products->random()->id,
            'user_id' => $kasir->id,
            'type' => 'out',
            'quantity' => 3,
            'notes' => 'Koreksi stok - produk rusak',
        ]);

        // Stock opname (type: in - selisih positif)
        StockAdjustment::create([
            'product_id' => $products->random()->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'notes' => 'Selisih stock opname',
        ]);
    }

    protected function createLowStockProducts(): void
    {
        // Products are already created with low stock in the main product list
        // This is just for demonstration
    }
}
