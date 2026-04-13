<?php

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->kasir = User::factory()->create(['role' => 'kasir']);
});

it('displays POS interface for kasir', function () {
    actingAs($this->kasir)
        ->get(route('kasir.pos.index'))
        ->assertSuccessful()
        ->assertViewIs('kasir.pos.index')
        ->assertViewHas('products');
});

it('displays POS interface for admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    actingAs($admin)
        ->get(route('kasir.pos.index'))
        ->assertSuccessful();
});

it('searches products via AJAX', function () {
    Product::factory()->create(['name' => 'Indomie Goreng']);
    Product::factory()->create(['name' => 'Aqua']);

    actingAs($this->kasir)
        ->get(route('kasir.pos.search', ['q' => 'Indo']))
        ->assertSuccessful()
        ->assertJsonCount(1);
});

it('processes a checkout successfully', function () {
    $product = Product::factory()->create(['price' => 5000, 'stock' => 10]);

    actingAs($this->kasir)
        ->postJson(route('kasir.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'cash' => 15000,
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'message', 'transaction']);

    assertDatabaseHas('transactions', ['total' => 10000, 'cash' => 15000, 'change' => 5000]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
});

it('rejects checkout with insufficient stock', function () {
    $product = Product::factory()->create(['price' => 5000, 'stock' => 1]);

    actingAs($this->kasir)
        ->postJson(route('kasir.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'cash' => 50000,
        ])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('rejects checkout with insufficient cash', function () {
    $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);

    actingAs($this->kasir)
        ->postJson(route('kasir.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'cash' => 5000,
        ])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('validates checkout request', function () {
    actingAs($this->kasir)
        ->postJson(route('kasir.pos.checkout'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items', 'cash']);
});

it('displays receipt for a transaction', function () {
    $product = Product::factory()->create(['price' => 5000, 'stock' => 10]);

    $response = actingAs($this->kasir)
        ->postJson(route('kasir.pos.checkout'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'cash' => 10000,
        ]);

    $transactionId = $response->json('transaction.id');

    actingAs($this->kasir)
        ->get(route('kasir.pos.receipt', $transactionId))
        ->assertSuccessful()
        ->assertViewIs('kasir.pos.receipt');
});
