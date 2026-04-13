<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'POS KASIR'),
            'store_address' => Setting::get('store_address', ''),
            'store_phone' => Setting::get('store_phone', ''),
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih!'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'store_phone' => ['nullable', 'string', 'max:20'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            // Clear cache for this setting
            Cache::forget("setting.{$key}");
        }

        ActivityLog::log('Update Pengaturan', 'Memperbarui pengaturan identitas toko');

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
