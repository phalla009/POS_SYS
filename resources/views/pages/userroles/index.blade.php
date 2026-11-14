@extends('layouts.master')

@section('pageTitle')
    Roles Listing
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <script src="{{ URL::asset('js/delete_form.js') }}"></script>
    <style>
        /* Filter row (same layout as products page) */
        .filter-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-controls .btn {
            flex-shrink: 0;
        }

        #noResultsRow { display: none; }

        @media (max-width: 768px) {
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls .btn-primary {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .filter-controls .form-group {
                width: 100%;
                min-width: 100% !important;
            }
        }
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

    <div class="content-section" id="roles">
        <h2><i class="fas fa-user-shield"></i> Roles Management</h2>

        <div class="filter-section">
            <div class="filter-controls">
                <a href="{{ route('userroles.create') }}"
                   class="btn btn-primary page-link-loading"
                   data-loading-text="Loading form..."
                   style="white-space:nowrap;">
                    <i class="fas fa-circle-plus"></i> Add New Role
                </a>

                {{-- Search (same style as products page, filters live in the browser) --}}
                <div class="form-group" style="position:relative; min-width:800px; margin:0;">
                    <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#b39b85;font-size:14px;pointer-events:none;"></i>
                    <input type="text"
                           id="roleSearch"
                           placeholder="Search Roles..."
                           autocomplete="off"
                           style="border-radius:24px; padding-left:38px;">
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Role Name</th>
                    <th>Description</th>
                    {{--                    <th>Created At</th>--}}
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="rolesTable">
                @forelse ($roles as $role)
                    <tr class="role-row">
                        <td data-label="No">#{{ $loop->iteration }}</td>
                        <td data-label="Role Name" style="color: black; font-weight: 600;">
                            @php
                                $icon    = 'fas fa-user';
                                $bgColor = '#7f8c8d';
                                switch(strtolower($role->role_name ?? '')) {
                                    case 'admin':   $icon = 'fas fa-user-shield'; $bgColor = '#e74c3c'; break;
                                    case 'manager': $icon = 'fas fa-user-tie';    $bgColor = '#27ae60'; break;
                                    case 'staff':   $icon = 'fas fa-user';        $bgColor = '#2980b9'; break;
                                }
                            @endphp
                            <i class="{{ $icon }}"
                               style="color:#fff; background:{{ $bgColor }}; border-radius:50%;
                                      padding:5px; margin-right:5px; font-size:14px; width:25px; height:25px;
                                      display:inline-flex; align-items:center; justify-content:center;">
                            </i>
                            {{ $role->role_name }}
                        </td>
                        <td data-label="Description">{{ $role->description ?? 'N/A' }}</td>
                        {{--                        <td data-label="Created At">{{ $role->created_at ? $role->created_at->format('d M, Y h:i A') :'N/A' }}</td>--}}
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('userroles.show', $role->id) }}"
                                   class="action-btn show-btn page-link-loading"
                                   data-loading-text="Loading details..."
                                   title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('userroles.edit', $role->id) }}"
                                   class="action-btn edit-btn page-link-loading"
                                   data-loading-text="Opening editor..."
                                   title="Edit Role">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button"
                                        class="action-btn delete-btn openDeleteModal"
                                        data-action="{{ route('userroles.destroy', $role->id) }}"
                                        title="Delete Role">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;" id="found">No roles found.</td>
                    </tr>
                @endforelse

                {{-- Shown when search yields no matches --}}
                <tr id="noResultsRow">
                    <td colspan="4" style="text-align:center; color:#6b7280;">
                        <i class="fas fa-search" style="margin-right:6px;"></i> No results match your search.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-delete-modal />

    <script>
        // Live search: filters by role name and description
        const roleSearch = document.getElementById('roleSearch');

        roleSearch.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#rolesTable .role-row');
            let visibleCount = 0;

            rows.forEach(function (row) {
                const name = row.querySelector('[data-label="Role Name"]')?.textContent.toLowerCase() ?? '';
                const desc = row.querySelector('[data-label="Description"]')?.textContent.toLowerCase() ?? '';

                const matches = name.includes(term) || desc.includes(term);
                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            document.getElementById('noResultsRow').style.display =
                (visibleCount === 0 && term !== '' && rows.length > 0) ? '' : 'none';
        });

        // No form here, so Enter should do nothing
        roleSearch.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    </script>

@endsection
