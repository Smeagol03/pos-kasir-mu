<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Konfirmasi Restorasi') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Restorasi Database</h3>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Peringatan Penting</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Proses ini akan:</p>
                                <ul class="list-disc pl-5 mt-1 space-y-1">
                                    <li>Mengganti seluruh data database saat ini</li>
                                    <li>Membutuhkan waktu beberapa menit untuk menyelesaikan proses</li>
                                    <li>Memerlukan restart layanan aplikasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-600 mb-2">Anda akan melakukan restorasi dari file:</p>
                    <div class="bg-gray-100 p-3 rounded-lg">
                        <span class="font-mono text-sm">📦 {{ $filename }}</span>
                    </div>
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('admin.backups.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">
                        Batal
                    </a>
                    
                    <a href="{{ route('admin.backups.restore', ['filename' => $filename, 'confirmed' => 1]) }}" 
                       class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">
                        Ya, Lanjutkan Restorasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>