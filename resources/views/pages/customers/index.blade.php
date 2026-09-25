@extends('layouts.master')

@section('pageTitle')
    Customers Listing
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <script src="{{ URL::asset('js/delete_form.js') }}"></script>

    <style>
        .status-active   { color: green; background-color: transparent !important; }
        .status-inactive { color: red;   background-color: transparent !important; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        thead tr { background-color: #f7f7f7; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: middle; word-wrap: break-word; }

        /* Filter section layout */
        .filter-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Search field */
        .search-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .search-wrapper .search-icon {
            position: absolute;
            left: 12px;
            color: #aaa;
            pointer-events: none;
        }
        #customerSearch {
            padding: 10px 12px 10px 34px;
            border: 1px solid #ddd;
            border-radius: 24px;
            font-size: 14px;
            width: 600px;
            max-width: 100%;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        #customerSearch:focus {
            border-color: #132f4f;
            box-shadow: 0 0 0 3px rgba(74,144,226,0.15);
        }
        #noResultsRow { display: none; }

        /* ── Responsive Enhancements ── */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Tablet & Mobile Layout (<= 768px) */
        @media (max-width: 768px) {
            .filter-section .filter-controls {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
            }

            .filter-controls .btn-primary {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .search-wrapper {
                width: 100%;
            }

            #customerSearch {
                width: 100% !important;
            }
        }

        /* Mobile Stacked Table Cards (<= 576px) */
        @media (max-width: 576px) {
            .table-container table,
            .table-container thead,
            .table-container tbody,
            .table-container th,
            .table-container td,
            .table-container tr {
                display: block;
            }

            .table-container thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            .table-container tr {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                margin-bottom: 12px;
                padding: 12px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            }

            .table-container td {
                border: none;
                position: relative;
                padding: 8px 0 8px 45% !important;
                text-align: right !important;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                min-height: 36px;
            }

            .table-container td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 40%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: 600;
                color: #64748b;
                font-size: 13px;
            }

            .table-container td[data-label="Actions"] {
                padding-left: 0 !important;
                justify-content: flex-end;
                margin-top: 6px;
                border-top: 1px dashed #edf2f7;
                padding-top: 10px !important;
            }

            .table-container td[data-label="Actions"]::before {
                display: none;
            }

            .table-container td#found,
            .table-container tr#noResultsRow td {
                padding: 20px !important;
                text-align: center !important;
                justify-content: center;
            }

            .table-container td#found::before,
            .table-container tr#noResultsRow td::before {
                display: none;
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

    <div class="content-section" id="customers">
        <h2><i class="fas fa-users"></i> Customer Management</h2>

        <div class="filter-section">
            <div class="filter-controls">
                <a href="{{ route('customers.create') }}"
                   class="btn btn-primary page-link-loading"
                   data-loading-text="Loading add...">
                    <i class="fas fa-circle-plus"></i> Add New Customer
                </a>

                {{-- 🔍 Search field --}}
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           id="customerSearch"
                           placeholder="Search by name, phone..."
                           autocomplete="off">
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="customersTable">
                    @forelse ($customers as $customer)
                        <tr>
                            <td data-label="No">#{{ $loop->iteration }}</td>
                            <td data-label="Name"><i class="fas fa-user user-icon" style="margin-right: 6px;"></i>{{ $customer->name }}</td>
                            <td data-label="Gender">{{ ucfirst($customer->gender) }}</td>
                            <td data-label="Phone">{{ $customer->phone }}</td>
                            <td data-label="Status">
                                @if(strtolower($customer->status) === 'active')
                                    <i class="fas fa-check-circle" style="color:green;font-size:20px;"></i>
                                @elseif(strtolower($customer->status) === 'inactive')
                                    <i class="fas fa-times-circle" style="color:red;font-size:20px;"></i>
                                @endif
                            </td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <a href="{{ route('customers.show', $customer->id) }}"
                                       class="action-btn show-btn page-link-loading"
                                       data-loading-text="Loading details..." title="View Details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer->id) }}"
                                       class="action-btn edit-btn page-link-loading"
                                       data-loading-text="Opening editor..." title="Edit Customer">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button"
                                            class="action-btn delete-btn openDeleteModal"
                                            data-action="{{ route('customers.destroy', $customer->id) }}"
                                            title="Delete Customer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;" id="found">No customers found.</td>
                        </tr>
                    @endforelse

                    {{-- Shown by JS when search yields no matches --}}
                    <tr id="noResultsRow">
                        <td colspan="6" style="text-align:center;">No customers match your search.</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-delete-modal />

    <script>
        // 🔍 Live search — filters by Name and Phone
        document.getElementById('customerSearch').addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows  = document.querySelectorAll('#customersTable tr:not(#noResultsRow)');
            let visibleCount = 0;

            rows.forEach(function(row) {
                // Column indices: 1 = Name, 3 = Phone
                const name  = (row.cells[1]?.textContent || '').toLowerCase();
                const phone = (row.cells[3]?.textContent || '').toLowerCase();
                const match = name.includes(query) || phone.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            document.getElementById('noResultsRow').style.display =
                (visibleCount === 0 && query !== '') ? '' : 'none';
        });
    </script>

@endsection
