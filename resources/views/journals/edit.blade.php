@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col d-flex align-items-center">
                    <a href="{{ route('journals.index') }}" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-arrow-left"></i>
                    </a>      
                        <h2 class="page-title mb-0">Ubah Jurnal</h2> 
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>Terjadi Kesalahan!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form action="{{ route('journals.update', $journal->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3 align-items-center">
                                    @if(auth()->user()->hasRole('Super-Admin'))
                                        <div class="col-md-4 mb-3">
                                            <label for="license_id" class="form-label">Pilih Lisensi</label>
                                            <select name="license_id" id="license_id" class="form-select" disabled>
                                                @foreach ($licenses as $license)
                                                    <option value="{{ $license->id }}" 
                                                        {{ $journal->license_id == $license->id ? 'selected' : '' }}>
                                                        {{ $license->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="license_id" value="{{ $journal->license_id }}">
                                        </div>
                                    @endif

                                    <div class="col-md-4 mb-3">
                                        <label for="journal_code" class="required">No Transaksi</label>
                                        <input type="text" name="journal_code" 
                                            class="form-control" value="{{ old('journal_code', $journal->journal_code) }}" readonly>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="transaction_date">Tanggal Transaksi</label>
                                        <input type="date" name="transaction_date" class="form-control"
                                            value="{{ old('transaction_date', $journal->transaction_date) }}" required>
                                    </div>
                                </div>

                                <h5>Detail Akun</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Akun</th>
                                            <th>Deskripsi</th>
                                            <th>User</th>
                                            <th>Debit</th>
                                            <th>Kredit</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-rows">
                                        @if(isset($journal) && $journal->details)
                                            @foreach ($journal->details as $i => $detail)
                                                <tr>
                                                    {{-- Pilih Akun --}}
                                                    <td>
                                                        <select name="details[{{ $i }}][account_id]" 
                                                                class="form-select account-select" 
                                                                data-row="{{ $i }}" required>
                                                            <option value="{{ $detail->account_id }}" 
                                                                    data-person-type="{{ $detail->account->person_type }}" 
                                                                    selected>
                                                                {{ $detail->account->account_code }} - {{ $detail->account->account_name }}
                                                            </option>
                                                        </select>
                                                    </td>

                                                    {{-- Deskripsi --}}
                                                    <td>
                                                        <input type="text" 
                                                            name="details[{{ $i }}][description]" 
                                                            class="form-control"
                                                            value="{{ old("details.$i.description", $detail->description) }}">
                                                    </td>

                                                    <td>
                                                        <select name="details[{{ $i }}][person]" 
                                                                class="form-control select2 user-select" 
                                                                data-row="{{ $i }}" 
                                                                data-selected="{{ $detail->person ?? '' }}">
                                                            <option value="">-- Pilih User --</option>
                                                            @php
                                                                if ($detail->person_type === 'student') {
                                                                    $users = $students;
                                                                } elseif ($detail->person_type === 'employee') {
                                                                    $users = $employees;
                                                                } elseif ($detail->person_type === 'license') {
                                                                    $users = $licenseList;
                                                                } else {
                                                                    $users = collect();
                                                                }
                                                            @endphp
                                                            @foreach ($users as $u)
                                                                <option value="{{ $u->id }}" {{ $u->id == $detail->person ? 'selected' : '' }}>
                                                                    {{ $u->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        @if($detail->person)
                                                            <input type="hidden" name="details[{{ $i }}][person]" value="{{ $detail->person }}">
                                                        @endif
                                                    </td>

                                                    {{-- <td>
                                                            <select name="details[{{ $i }}][person]" 
                                                                    class="form-select user-select" 
                                                                    data-row="{{ $i }}" 
                                                                    data-selected="{{ $detail->person_id }}">
                                                                @if($detail->person)
                                                                    <option value="{{ $detail->person_id }}" selected>
                                                                        {{ $detail->person->name }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                    </td> --}}
                                                    
                                                    <td>
                                                        <input type="number" step="0.01" 
                                                            name="details[{{ $i }}][debit]" 
                                                            class="form-control debit-input" 
                                                            value="{{ old("details.$i.debit", $detail->debit) }}"
                                                            {{ $detail->credit ? 'disabled' : '' }}>

                                                        @if($detail->credit)
                                                            <input type="hidden" name="details[{{ $i }}][debit]" value="{{ $detail->debit }}">
                                                        @endif
                                                    </td>

                                                    
                                                    <td>
                                                        <input type="number" step="0.01" 
                                                            name="details[{{ $i }}][credit]" 
                                                            class="form-control credit-input" 
                                                            value="{{ old("details.$i.credit", $detail->credit) }}"
                                                            {{ $detail->debit ? 'disabled' : '' }}>

                                                        @if($detail->debit)
                                                            <input type="hidden" name="details[{{ $i }}][credit]" value="{{ $detail->credit }}">
                                                        @endif
                                                    </td>
                                                    <td><button type="button" class="btn btn-sm btn-danger remove-row" title="Hapus">
                                                                <i class="ti ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="6"><button type="button" id="add-row" class="btn btn-sm btn-primary text-white">Tambah Baris</button></td>
                                        </tr>
                                        <tr>
                                            <th colspan="3">Subtotal</th>
                                            <th id="subtotal-debit">{{ $journal->details->sum('debit') }}</th>
                                            <th id="subtotal-credit">{{ $journal->details->sum('credit') }}</th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>

                                @php
                                    $isBalanced = $journal->details->sum('debit') == $journal->details->sum('credit');
                                @endphp

                                <div id="balance-status" 
                                    class="mt-2 fw-bold {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                                    {{ $isBalanced ? '✅ Seimbang' : '❌ Tidak Seimbang' }}
                                </div>

                                <div class="col-md-6 mb-3">
                                        <label for="description">Keterangan</label>
                                        <textarea name="description" class="form-control">{{ old('description', $journal->description) }}</textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="enclosure" class="form-label">Lampiran (PDF / Gambar)</label>
                                    <input type="file" name="enclosure" class="form-control">

                                    @if($journal->enclosure)
                                        <div class="mt-2">
                                            <p>File saat ini:</p>
                                            @if(\Illuminate\Support\Str::endsWith($journal->enclosure, ['.jpg', '.jpeg', '.png']))
                                                <img src="{{ asset('storage/'.$journal->enclosure) }}" alt="Enclosure" width="200">
                                            @elseif(\Illuminate\Support\Str::endsWith($journal->enclosure, ['.pdf']))
                                                <a href="{{ asset('storage/'.$journal->enclosure) }}" target="_blank">Lihat PDF</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary text-white">Update</button>
                                </div>
                                
                                @if(!auth()->user()->hasRole('Super-Admin'))
                                        <input type="hidden" id="activeLicenseId" value="{{ $activeLicenseId }}">
                                @endif
                            
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')

<script>
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });

    let accountsData = [];

    /** 🔹 Ambil akun berdasarkan lisensi aktif */
    function loadAccountsByLicense(licenseId) {
        if (!licenseId) return;

        $.get(`/get-accounts-by-license/${licenseId}`, function (data) {
            accountsData = data;

            // Update semua select akun yang kosong (bukan edit)
            $('.account-select').each(function () {
                if (!$(this).val()) {
                    renderAccountOptions($(this));
                }
            });
        });
    }

    /** 🔹 Render akun ke dropdown */
    function renderAccountOptions($select) {
        $select.empty().append('<option value="">-- Pilih Akun --</option>');
        $.each(accountsData, function (_, account) {
            $select.append(
                `<option value="${account.id}" 
                         data-code="${account.account_code}" 
                         data-name="${account.account_name}"
                         data-person-type="${account.person_type}">
                    ${account.account_code} - ${account.account_name}
                 </option>`
            );
        });
    }

    /** 🔹 Render user sesuai person_type */
    function renderUserOptions($select, personType, selected = null) {
        $select.empty().append('<option value="">-- Pilih User --</option>');

        if (!personType) return;

        let urlMap = {
            student: '/get-students',
            employee: '/get-employees',
            licenseholder: '/get-licenseholders',
            license: '/get-licenses'
        };

        if (!urlMap[personType]) return;

        $.get(urlMap[personType], function (data) {
            $.each(data, function (_, user) {
                $select.append(
                    `<option value="${user.id}" ${selected == user.id ? 'selected' : ''}>
                        ${user.name}
                     </option>`
                );
            });
        });
    }

    /** 🔹 Change event: pilih akun → render user sesuai person_type */
    $(document).on('change', '.account-select', function () {
        let row = $(this).data('row');
        let personType = $(this).find(':selected').data('person-type');
        let $userSelect = $(`.user-select[data-row="${row}"]`);

        renderUserOptions($userSelect, personType);
    });

    /** 🔹 Lock account & user kalau data lama (edit mode) */
    $('.account-select').each(function () {
        let selectedVal = $(this).val();
        let row = $(this).data('row');
        let $userSelect = $(`.user-select[data-row="${row}"]`);
        let selectedUser = $userSelect.data('selected');

        if (selectedVal) {
            $(this).prop('disabled', false);
            $(this).after(
                `<input type="hidden" name="${$(this).attr('name')}" value="${selectedVal}">`
            );
        }

        if (selectedUser) {
            $userSelect.prop('disabled', false);
            $userSelect.after(
                `<input type="hidden" name="${$userSelect.attr('name')}" value="${selectedUser}">`
            );

            // 🔹 render user lama biar tetap tampil di dropdown
            let personType = $(this).find(':selected').data('person-type');
            renderUserOptions($userSelect, personType, selectedUser);
        }
    });

    /** 🔹 Ganti lisensi → load akun baru */
    $('#license_id').on('change', function () {
        const licenseId = $(this).val();
        $('#activeLicenseId').val(licenseId);
        loadAccountsByLicense(licenseId);
    });

    // Jalankan load pertama kali
    const selectedLicense = $('#license_id').length
        ? $('#license_id').val()
        : $('#activeLicenseId').val();
    loadAccountsByLicense(selectedLicense);

    /** 🔹 Init user lama di edit form */
    $('.user-select').each(function () {
        let $select = $(this);
        let row = $select.data('row');
        let selectedUser = $select.data('selected');
        let personType = $(`.account-select[data-row="${row}"] option:selected`).data('person-type');

        // Render ulang user sesuai personType
        renderUserOptions($select, personType, selectedUser);
    });


    /** 🔹 Tambah baris baru */
    $('#add-row').click(function () {
        const rowCount = $('#detail-rows tr').length;
        const newRow = `
            <tr>
                <td>
                    <select name="details[${rowCount}][account_id]" 
                            class="form-select account-select" 
                            data-row="${rowCount}" required></select>
                </td>
                <td><input type="text" name="details[${rowCount}][description]" class="form-control"></td>
                <td>
                    <select name="details[${rowCount}][person]" 
                            class="form-select user-select" 
                            data-row="${rowCount}"></select>
                </td>
                <td><input type="number" step="0.01" name="details[${rowCount}][debit]" class="form-control debit-input"></td>
                <td><input type="number" step="0.01" name="details[${rowCount}][credit]" class="form-control credit-input"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row" title="Hapus">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#detail-rows').append(newRow);

        // Isi opsi akun
        const $newAccountSelect = $('#detail-rows tr:last .account-select');
        renderAccountOptions($newAccountSelect);
    });

    /** 🔹 Hapus baris */
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        calculateSubtotals();
    });

    /** 🔹 Hitung subtotal otomatis */
    function calculateSubtotals() {
        let totalDebit = 0, totalCredit = 0;
        $('#detail-rows tr').each(function() {
            const debit  = parseFloat(($(this).find('.debit-input').val() || '0').replace(/,/g, '')) || 0;
            const credit = parseFloat(($(this).find('.credit-input').val() || '0').replace(/,/g, '')) || 0;

            totalDebit  += debit;
            totalCredit += credit;
        });

        $('#subtotal-debit').text(totalDebit.toLocaleString('id-ID'));
        $('#subtotal-credit').text(totalCredit.toLocaleString('id-ID'));

        const isBalanced = totalDebit === totalCredit;

        $('#balance-status')
            .text(isBalanced ? '✅ Seimbang' : '❌ Tidak Seimbang')
            .toggleClass('text-success', isBalanced)
            .toggleClass('text-danger', !isBalanced);
    }

    /** 🔹 Event listener input debit/kredit */
    $(document).on('input', '.debit-input, .credit-input', function() {
        const val = parseFloat($(this).val());
        if (val < 0) $(this).val('');
        if ($(this).hasClass('debit-input')) {
            $(this).closest('tr').find('.credit-input').val('');
        } else {
            $(this).closest('tr').find('.debit-input').val('');
        }
        calculateSubtotals();
    });

    /** 🔹 Validasi submit */
    $('form').on('submit', function (e) {
        let totalDebit = 0, totalCredit = 0;
        $('#detail-rows tr').each(function() {
            totalDebit  += parseFloat($(this).find('.debit-input').val())  || 0;
            totalCredit += parseFloat($(this).find('.credit-input').val()) || 0;
        });

        if (totalDebit !== totalCredit) {
            e.preventDefault();
            alert('Transaksi tidak balance! Jumlah Debit dan Kredit harus sama.');
        }
    });
});
</script>

@endpush


