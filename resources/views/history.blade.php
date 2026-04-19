@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex justify-between items-center">
            <div>
                <a href="/dashboard" class="text-gray-500 hover:text-gray-700 mb-2 inline-flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </a>
                <h2 class="text-3xl font-extrabold text-gray-900">Riwayat STO Anda</h2>
                <p class="text-gray-600">Daftar buku yang telah Anda verifikasi.</p>
            </div>
        </div>

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Buku</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode Item</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kondisi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal STO</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider pr-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="history-list" class="bg-white divide-y divide-gray-200">
                        <!-- Loading state -->
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat riwayat...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="empty-state" class="hidden py-20 text-center">
                <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Belum ada riwayat STO.</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-xl font-bold text-gray-800">Ubah Kondisi Buku</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-500 uppercase mb-1">Judul Buku</p>
                <p id="modal-title" class="text-lg font-bold text-gray-800">-</p>
                <p id="modal-item-code" class="text-sm font-mono text-indigo-600 font-bold mt-1">-</p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3">Pilih Kondisi (Bisa lebih dari satu)</label>
                <div id="modal-condition-grid" class="grid grid-cols-2 gap-2 h-48 overflow-y-auto pr-2 custom-scrollbar">
                    @php
                        $options = [
                            "Baik", "Barcode lepas", "Barcode rusak", "Barcode salah", "Barcode tidak ada",
                            "Label lepas", "Label salah", "Halaman sobek", "Halaman rusak", "Halaman lepas",
                            "Halaman hilang", "Sampul sobek", "Sampul rusak", "Sampul lepas", "Sampul tidak ada",
                            "Sampul rusak kena air", "Sampul rusak dimakan kutu", "Sampul rusak dimakan rayap", "Sampul rusak kena debu"
                        ];
                    @endphp
                    @foreach($options as $option)
                        <label class="flex items-center p-2 rounded border border-gray-200 cursor-pointer hover:bg-gray-50 transition duration-150">
                            <input type="checkbox" name="modal-condition" value="{{ $option }}" class="modal-condition-checkbox w-4 h-4 text-indigo-500 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-xs text-gray-600 leading-tight">{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan Tambahan (Opsional)</label>
                <textarea id="modal-notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Catatan tambahan..."></textarea>
            </div>

            <div class="flex gap-3 mt-8">
                <button onclick="closeEditModal()" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">
                    Batal
                </button>
                <button id="save-change-btn" class="flex-1 px-4 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition flex items-center justify-center">
                    <span>Simpan Perubahan</span>
                </button>
            </div>
            <p class="text-center text-[10px] text-gray-400 mt-4 leading-normal italic">
                *Mengubah kondisi tidak akan menambah atau mengubah nominal komisi yang sudah didapatkan.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ccc; }
</style>
<script>
    const token = localStorage.getItem('token');
    let currentOpnameId = null;

    const fetchHistory = async () => {
        const listContainer = document.getElementById('history-list');
        const emptyState = document.getElementById('empty-state');
        
        try {
            const response = await fetch('/api/history', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (data.length === 0) {
                listContainer.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            listContainer.innerHTML = '';
            data.forEach(item => {
                const createdDate = new Date(item.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                const updatedDate = new Date(item.updated_at).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                const isEdited = item.created_at !== item.updated_at;
                
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 transition';
                tr.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900">${item.book.title}</div>
                        <div class="text-xs text-gray-500">${item.book.author || 'No Author'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-md text-xs font-mono font-bold border border-indigo-100">
                            ${item.item_code}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            ${item.condition ? item.condition.split(', ').map(c => `<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] whitespace-nowrap">${c}</span>`).join('') : '-'}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-600 italic max-w-xs truncate" title="${item.notes || ''}">
                            ${item.notes || '-'}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-xs text-gray-900 font-medium">${createdDate}</div>
                        ${isEdited ? `<div class="text-[10px] text-orange-500 mt-1 font-semibold flex items-center"><i class="fa-solid fa-pen-nib mr-1"></i> Edit: ${updatedDate}</div>` : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium pr-10">
                        <button onclick='openEditModal(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                    </td>
                `;
                listContainer.appendChild(tr);
            });
        } catch (error) {
            console.error('Error fetching history:', error);
        }
    };

    window.openEditModal = (item) => {
        currentOpnameId = item.id;
        document.getElementById('modal-title').innerText = item.book.title;
        document.getElementById('modal-item-code').innerText = item.item_code;
        document.getElementById('modal-notes').value = item.notes || '';
        
        // Reset checkboxes
        const currentConditions = item.condition ? item.condition.split(', ') : [];
        document.querySelectorAll('.modal-condition-checkbox').forEach(cb => {
            cb.checked = currentConditions.includes(cb.value);
        });

        document.getElementById('edit-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeEditModal = () => {
        document.getElementById('edit-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    };

    document.getElementById('save-change-btn').addEventListener('click', async () => {
        const btn = document.getElementById('save-change-btn');
        const checkedConditions = Array.from(document.querySelectorAll('.modal-condition-checkbox:checked'))
            .map(cb => cb.value);
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Menyimpan...';

        try {
            const response = await fetch(`/api/stock-opname/${currentOpnameId}`, {
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    condition: checkedConditions.join(', '),
                    notes: document.getElementById('modal-notes').value
                })
            });

            if (response.ok) {
                closeEditModal();
                fetchHistory(); // Refresh list
            } else {
                alert('Gagal menyimpan perubahan');
            }
        } catch (error) {
            console.error('Error updating:', error);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan Perubahan</span>';
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (!token) window.location.href = '/login';
        fetchHistory();
    });
</script>
@endpush
@endsection
