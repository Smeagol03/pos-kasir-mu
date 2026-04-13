<?php

use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->kasir = User::factory()->create(['role' => 'kasir']);
});

it('displays transactions index for admin', function () {
    Transaction::factory()->count(3)->create(['user_id' => $this->kasir->id]);

    actingAs($this->admin)
        ->get(route('admin.transactions.index'))
        ->assertSuccessful()
        ->assertViewIs('admin.transactions.index')
        ->assertViewHas('transactions');
});

it('denies transactions access for non-admin users', function () {
    actingAs($this->kasir)
        ->get(route('admin.transactions.index'))
        ->assertForbidden();
});

it('shows transaction details', function () {
    $transaction = Transaction::factory()->create(['user_id' => $this->kasir->id]);

    actingAs($this->admin)
        ->get(route('admin.transactions.show', $transaction->id))
        ->assertSuccessful()
        ->assertViewIs('admin.transactions.show')
        ->assertViewHas('transaction');
});

it('filters transactions by date range', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->kasir->id,
        'created_at' => now(),
    ]);

    actingAs($this->admin)
        ->get(route('admin.transactions.index', [
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->toDateString(),
        ]))
        ->assertSuccessful()
        ->assertViewHas('transactions');
});

it('exports transactions to CSV', function () {
    Transaction::factory()->count(2)->create(['user_id' => $this->kasir->id]);

    actingAs($this->admin)
        ->get(route('admin.transactions.export'))
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=utf-8');
});
