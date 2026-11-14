@extends('layouts.master')
@section('pageTitle', 'Backup Database')

@section('headerBlock')
<link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
<style>
    @keyframes bkIn { from { transform: scale(.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    .bk-modal {
        display: none; position: fixed; inset: 0; z-index: 10001;
        background: rgba(15,23,42,.5); backdrop-filter: blur(4px);
        align-items: center; justify-content: center; padding: 20px;
    }
    .bk-modal.open { display: flex; }
    .bk-modal-box {
        background: #fff; border-radius: 16px; max-width: 400px; width: 100%;
        padding: 32px 28px 24px; text-align: center;
        box-shadow: 0 24px 60px rgba(0,0,0,.18);
        animation: bkIn .25s ease;
    }
    .bk-modal-icon {
        width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 16px;
        background: #fee2e2; color: #ef4444; font-size: 26px;
        display: flex; align-items: center; justify-content: center;
    }
    .bk-modal-box h3 { margin: 0 0 8px; font-size: 20px; color: #0f172a; }
    .bk-modal-box p  { margin: 0 0 22px; font-size: 14px; color: #64748b; line-height: 1.5; }
    .bk-modal-actions { display: flex; gap: 10px; }
    .bk-modal-actions button { flex: 1; }

    .file-check, #selectAll { width: 16px; height: 16px; cursor: pointer; accent-color: #e74c3c; }
    tr.row-selected { background: #fff7f7; }
    .bk-error { color:#dc2626; margin:10px 0; font-weight:600; }
</style>
@endsection

@section('content')

@if(session('success'))
    <div id="successMessage" class="custom-success">
        <div class="success-content">
            <span class="success-icon">✔</span>
            <span class="success-text">{{ session('success') }}</span>
        </div>
        <div class="progress-bar"></div>
    </div>
@endif

<div class="content-section">
    <h2><i class="fas fa-database"></i> Backup Database</h2>

    @if(session('error'))
        <div class="bk-error">{{ session('error') }}</div>
    @endif

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:10px;">
        <form action="{{ route('backup.store') }}" method="POST"
              onsubmit="document.getElementById('loading-text').innerText='Backing up...';
                        document.getElementById('loading-overlay').style.display='flex';">
            @csrf
            <button type="submit" class="btn btn-success" style="min-width:200px;">
                <i class="fas fa-download"></i> Backup Now
            </button>
            @if(count($files) > 0)
                <button type="button" id="deleteSelectedBtn" class="btn btn-danger" style="min-width:200px; margin-top: 5px;" disabled>
                    <i class="fas fa-trash"></i> Delete Selected (<span id="selCount">0</span>)
                </button>
            @endif
        </form>

        
    </div>

    <h3 style="margin-top:20px;">Previous Backups</h3>

    <form id="deleteForm" action="{{ route('backup.delete') }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="table-container" role="region" aria-label="Backup files table" style="margin-top: 20px;">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th style="width:36px;"><input type="checkbox" id="selectAll" title="Select all"></th>
                    <th>File</th>
                    <th>Size</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($files as $f)
                    <tr>
                        <td data-label="No">#{{ $loop->iteration }}</td>
                        <td data-label="Select">
                            <input type="checkbox" class="file-check" name="files[]" value="{{ $f['name'] }}">
                        </td>
                        <td data-label="File" style="color: black; font-weight: 600;">{{ $f['name'] }}</td>
                        <td data-label="Size">{{ $f['size'] }}</td>
                        <td data-label="Date">{{ $f['date'] }}</td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('backup.download', $f['name']) }}"
                                   class="action-btn show-btn"
                                   title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;" id="found">No backups yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

{{-- Delete confirm modal --}}
<div class="bk-modal" id="deleteModal">
    <div class="bk-modal-box">
        <div class="bk-modal-icon"><i class="fas fa-trash"></i></div>
        <h3>Delete Backups?</h3>
        <p>You are about to delete <strong id="modalCount">0</strong> backup file(s). This action cannot be undone.</p>
        <div class="bk-modal-actions">
            <button type="button" class="btn btn-secondary" id="modalCancel"><i class="fas fa-times"></i> Cancel</button>
            <button type="button" class="btn btn-danger" id="modalConfirm"><i class="fas fa-check"></i> Yes, Delete</button>
        </div>
    </div>
</div>

<script>
    const selectAll = document.getElementById('selectAll');
    const checks    = document.querySelectorAll('.file-check');
    const delBtn    = document.getElementById('deleteSelectedBtn');
    const selCount  = document.getElementById('selCount');
    const modal     = document.getElementById('deleteModal');

    function updateState() {
        const n = document.querySelectorAll('.file-check:checked').length;
        if (selCount) selCount.textContent = n;
        if (delBtn) delBtn.disabled = n === 0;

        checks.forEach(c => c.closest('tr').classList.toggle('row-selected', c.checked));

        if (selectAll) {
            selectAll.checked = checks.length > 0 && n === checks.length;
            selectAll.indeterminate = n > 0 && n < checks.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks.forEach(c => c.checked = this.checked);
            updateState();
        });
    }
    checks.forEach(c => c.addEventListener('change', updateState));

    if (delBtn) {
        delBtn.addEventListener('click', function () {
            document.getElementById('modalCount').textContent =
                document.querySelectorAll('.file-check:checked').length;
            modal.classList.add('open');
        });
    }
    document.getElementById('modalCancel').addEventListener('click', () => modal.classList.remove('open'));
    document.getElementById('modalConfirm').addEventListener('click', () => document.getElementById('deleteForm').submit());
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.classList.remove('open'); });
</script>
@endsection