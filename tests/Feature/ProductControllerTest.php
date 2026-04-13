<?php

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('displays products index for admin', function () {
    Product::factory()->count(3)->create();

    actingAs($this->admin)
        ->get(route('admin.products.index'))
        ->assertSuccessful()
        ->assertViewIs('admin.products.index')
        ->assertViewHas('products');
});

it('denies access for non-admin users', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);

    actingAs($kasir)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});

it('shows product create form', function () {
    actingAs($this->admin)
        ->get(route('admin.products.create'))
        ->assertSuccessful()
        ->assertViewIs('admin.products.create');
});

it('stores a new product', function () {
    $productData = [
        'name' => 'Test Product',
        'price' => 10000,
        'purchase_price' => 7000,
        'stock' => 50,
        'barcode' => '1234567890',
    ];

    actingAs($this->admin)
        ->post(route('admin.products.store'), $productData)
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('products', ['name' => 'Test Product']);
});

it('validates required fields on store', function () {
    actingAs($this->admin)
        ->post(route('admin.products.store'), [])
        ->assertSessionHasErrors(['name', 'price', 'stock']);
});

it('shows product edit form', function () {
    $product = Product::factory()->create();

    actingAs($this->admin)
        ->get(route('admin.products.edit', $product->id))
        ->assertSuccessful()
        ->assertViewIs('admin.products.edit')
        ->assertViewHas('product');
});

it('updates an existing product', function () {
    $product = Product::factory()->create(['name' => 'Old Name']);

    actingAs($this->admin)
        ->put(route('admin.products.update', $product->id), [
            'name' => 'Updated Name',
            'price' => $product->price,
            'purchase_price' => $product->purchase_price,
            'stock' => $product->stock,
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Name']);
});

it('soft deletes a product', function () {
    $product = Product::factory()->create();

    actingAs($this->admin)
        ->delete(route('admin.products.destroy', $product->id))
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});
