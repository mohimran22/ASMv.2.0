@extends('tablar::page')

@section('content')
    <style>
        /* Select2 menyembunyikan <select> asli (1px, absolute). Pastikan aturan lebar
           .jurnal-table .form-select tidak ikut melebarkan select tersembunyi itu,
           karena itu yang membuat halaman bisa scroll horizontal. */
        .jurnal-table-wrapper { position: relative; }
        .jurnal-table td { position: relative; }
        .jurnal-table td select.select2-hidden-accessible {
            left: 0;
            top: 0;
            width: 1px !important;
            height: 1px !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
    </style>

    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col d-flex align-items-center">
                    <a href="{{ route('journals.index') }}" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-arrow-left"></i>
                    </a>      
                        <h2 class="page-title mb-0">Tambah Jurnal</h2> 
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

                            <form action="{{ route('journals.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                    <div class="row mb-3 align-items-center">
                                        {{-- Filter Lisensi (hanya untuk Super Admin) --}}
                                        @if(auth()->user()->hasRole('Super-Admin'))
                                            <div class="col-md-4 mb-3">
                                                <label for="license_id" class="form-label required">Filter Lisensi</label>
                                                <select name="license_id" id="license_id" class="form-select select2" required>
                                                    <option value="">-- Pilih Lisensi --</option>
                                                    @foreach ($licenses as $license)
                                                        <option value="{{ $license->id }}" 
                                                            {{ $activeLicenseId == $license->id ? 'selected' : '' }}>
                                                            {{ $license->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            {{-- Kalau bukan Super Admin, tetap pakai hidden --}}
                                            <input type="hidden" name="license_id" value="{{ $activeLicenseId }}">
                                        @endif

                                        {{-- No Transaksi --}}
                                        <div class="col-md-4 mb-3">
                                            <label for="journal_code" class="required">No Transaksi</label>
                                            <input type="text" id="journal_code" name="journal_code" 
                                                class="form-control" value="{{ $journalCode }}" readonly>
                                        </div>

                                        {{-- Tanggal Transaksi --}}
                                        <div class="col-md-4 mb-3">
                                            <label for="transaction_date" class="required">Tanggal Transaksi</label>
                                            <input type="date" id="transaction_date" name="transaction_date" class="form-control" required>
                                            <small id="period-warning" class="text-danger d-none">
                                                ⚠️ Periode sudah ditutup
                                            </small>
                                        </div>
                                    </div>
                        
                                <h5>Detail Akun</h5>
                                <div class="jurnal-table-wrapper">
                                    <small class="text-muted d-md-none d-block mb-1">Geser ke samping untuk melihat semua kolom →</small>
                                    <table class="table table-bordered jurnal-table">
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
                                            <tr>
                                                <td>
                                                    <select name="details[0][account_id]" class="form-select select2 account-select" data-row="0" required>
                                                        <option value="account">-- Pilih Akun --</option>
                                                            @foreach ($accounts as $account)
                                                                <option value="{{ $account->id }}"
                                                                    data-code="{{ $account->account_code }}"
                                                                    data-name="{{ $account->account_name }}"
                                                                    data-person-type="{{ $account->person_type }}">
                                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                                </option>
                                                            @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="text" name="details[0][description]" class="form-control"></td>
                                                <td>
                                                    <select name="details[0][person]" class="form-select select2 user-select" data-row="0">
                                                        <option value="">-- Pilih User --</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" inputmode="decimal" autocomplete="off" name="details[0][debit]" class="form-control debit-input"></td>
                                                <td><input type="text" inputmode="decimal" autocomplete="off" name="details[0][credit]" class="form-control credit-input"></td>
                                                <td><button type="button" class="btn btn-sm btn-danger remove-row" title="Hapus">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6"><button type="button" id="add-row" class="btn btn-sm btn-primary text-white">Tambah Baris</button></td>
                                            </tr>
                                            <tr>
                                                <th colspan="3">Subtotal</th>
                                                <th id="subtotal-debit">0</th>
                                                <th id="subtotal-credit">0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                    @php
                                        $isBalanced = $journal->details->sum('debit') == $journal->details->sum('credit');
                                    @endphp
                                    <div id="balance-status" 
                                        class="mt-2 fw-bold {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                                        {{ $isBalanced ? '✅ Seimbang' : '❌ Tidak Seimbang' }}
                                    </div>

                                <div class="col-md-4 mb-3">
                                    <label for="description">Keterangan</label>
                                    <textarea name="description" class="form-control"></textarea>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="enclosure" class="form-label">Lampiran</label>
                                    <input type="file" name="enclosure" class="form-control">
                                    @error('enclosure')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success text-white">Simpan Data</button>
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
    /** 🔹 Inisialisasi Select2 global */
    $('.select2').select2({ width: '100%' });

    let accountsData = [];

    /** Format Rupiah: 1.234.567 atau 1.234.567,5 (koma = desimal) */
    function parseRp(v) {
        if (v === null || v === undefined) return 0;
        const n = parseFloat(String(v).replace(/\./g, '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function formatRpInput(el) {
        const raw = el.value;
        const pos = el.selectionStart ?? raw.length;
        const sig = raw.slice(0, pos).replace(/[^\d,]/g, '').length; // digit/koma sebelum kursor
        const parts = raw.replace(/[^\d,]/g, '').split(',');
        let intPart = parts[0].replace(/^0+(?=\d)/, '');
        const dec = parts.length > 1 ? parts.slice(1).join('').slice(0, 2) : null;
        if (intPart === '' && dec !== null) intPart = '0';
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        const out = intPart + (dec !== null ? ',' + dec : '');
        el.value = out;

        let newPos = out.length;
        if (sig === 0) {
            newPos = 0;
        } else {
            let count = 0;
            for (let i = 0; i < out.length; i++) {
                if (/[\d,]/.test(out[i])) count++;
                if (count >= sig) { newPos = i + 1; break; }
            }
        }
        try { el.setSelectionRange(newPos, newPos); } catch (e) {}
    }

    /** ======================================================
     *  1. Ambil akun & kode jurnal berdasarkan lisensi aktif
     * ====================================================== */
    function loadAccountsByLicense(licenseId) {
        if (!licenseId) return;

        // Ambil akun
        $.get(`/get-accounts-by-license/${licenseId}`, function (data) {
            accountsData = data;

            // Update semua dropdown akun yang sudah ada
            $('.account-select').each(function () {
                renderAccountOptions($(this));
            });
        });

        // Ambil kode jurnal terbaru
        $('#journal_code').val('Loading...');
        $.get(`/journals/next-code/${licenseId}`)
            .done(function (res) {
                $('#journal_code').val(res.next_code);
            })
            .fail(function () {
                $('#journal_code').val('');
                alert('Gagal mengambil kode jurnal');
            });
    }

    /** ======================================================
     *  2. Render akun ke dropdown
     * ====================================================== */
    function renderAccountOptions($select) {
        $select.empty().append('<option value="">-- Pilih Akun --</option>');

        $.each(accountsData, function (_, account) {
            $select.append(`
                <option value="${account.id}" 
                        data-code="${account.account_code}" 
                        data-person-type="${account.person_type}">
                    ${account.account_code} - ${account.account_name}
                </option>
            `);
        });

        $select.select2({ placeholder: "-- Pilih Akun --", width: '100%' });
    }

    /** ======================================================
     *  3. Render dropdown user sesuai person_type
     * ====================================================== */
    const userCache = {};

    function renderUserOptions($select, type) {
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2("destroy");
        }

        $select.empty().append('<option value="">-- Pilih User --</option>');

        let url = '';
        if (type === "student") url = '/get-students';
        else if (type === "employee") url = '/get-employees';
        else if (type === "licenseholder") url = '/get-licenseholders';
        else if (type === "license") url = '/get-licenses';

        if (url) {
            if (userCache[type]) {
                // Pakai cache kalau sudah ada
                $.each(userCache[type], function (_, user) {
                    $select.append(`<option value="${user.id}">${user.name}</option>`);
                });
                initSelect2WithCreate($select, type);
            } else {
                $.get(url, function (data) {
                    userCache[type] = data;
                    $.each(data, function (_, user) {
                        $select.append(`<option value="${user.id}">${user.name}</option>`);
                    });
                    initSelect2WithCreate($select, type);
                });
            }
        } else {
            initSelect2WithCreate($select, type);
        }
    }

    /** ======================================================
     *  4. Select2 user dengan fitur input manual
     * ====================================================== */
    function initSelect2WithCreate($select, type) {
        if ($select.hasClass("select2-hidden-accessible")) return;
        
        $select.select2({
            placeholder: "-- Input manual jika tidak ada User --",
            width: '100%',
            tags: true,
            createTag: function (params) {
                let term = $.trim(params.term);
                if (term === '') return null;
                return { id: term, text: term, newOption: true };
            },
            templateResult: function (data) {
                let $result = $("<span></span>").text(data.text);
                if (data.newOption) {
                    $result.append(" <em>(tekan Enter)</em>");
                }
                return $result;
            }
        });

        $select.on('select2:select', function (e) {
            let data = e.params.data;
            if (data.newOption) {
                console.log("Input manual user:", data.text);
            }
        });
    }

    /** ======================================================
     *  5. Saat ganti lisensi → refresh akun & kode jurnal
     * ====================================================== */
    $('#license_id').on('change', function () {
        const licenseId = $(this).val();
        $('#activeLicenseId').val(licenseId);
        loadAccountsByLicense(licenseId);
    });

    /** ======================================================
     *  6. Load awal saat halaman dibuka
     * ====================================================== */
    const selectedLicense = $('#license_id').length
        ? $('#license_id').val()
        : $('#activeLicenseId').val();
    loadAccountsByLicense(selectedLicense);

    /** ======================================================
     *  7. Tambah baris baru
     * ====================================================== */
    $('#add-row').click(function () {
        console.log("Tambah baris dipanggil");
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
                <td><input type="text" inputmode="decimal" autocomplete="off" name="details[${rowCount}][debit]" class="form-control debit-input"></td>
                <td><input type="text" inputmode="decimal" autocomplete="off" name="details[${rowCount}][credit]" class="form-control credit-input"></td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row" title="Hapus">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#detail-rows').append(newRow);

        // Render opsi akun
        const $newAccountSelect = $('#detail-rows tr:last .account-select');
        renderAccountOptions($newAccountSelect);

        // Siapkan select user manual
        const $newUserSelect = $('#detail-rows tr:last .user-select');
        initSelect2WithCreate($newUserSelect, null);
    });

    /** ======================================================
     *  8. Hapus baris
     * ====================================================== */
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        calculateSubtotals();
    });

    /** ======================================================
     *  9. Daftar akun debit & kredit only
     * ====================================================== */
    const debitOnlyAccounts = new Set([
        "151","152","153","154","155","121","122","123","124","110",
        "131","132","141","142","144","304","305"
    ]);

    const creditOnlyAccounts = new Set([
        "301","302","303","161","162","163"
    ]);

    /** ======================================================
     * 10. Saat ganti akun → render user + disable debit/kredit
     * ====================================================== */
    $(document).on('change', '.account-select', function () {
        const $row = $(this).closest('tr');
        const personType = $(this).find(':selected').data('person-type');
        const accountCode = String($(this).find(':selected').data('code') || "");

        // Render user sesuai type
        renderUserOptions($row.find('.user-select'), personType);

        // Reset input debit & kredit
        const $debit = $row.find('.debit-input');
        const $credit = $row.find('.credit-input');
        $debit.prop('disabled', false).val('');
        $credit.prop('disabled', false).val('');

        if (debitOnlyAccounts.has(accountCode)) {
            $credit.prop('disabled', true).val('');
        } else if (creditOnlyAccounts.has(accountCode)) {
            $debit.prop('disabled', true).val('');
        } else {
            // Fallback by digit pertama
            const firstDigit = accountCode.charAt(0);
            if (firstDigit === "2" || firstDigit === "4") {
                $debit.prop('disabled', true).val('');
            } else if (firstDigit === "5" || firstDigit === "6") {
                $credit.prop('disabled', true).val('');
            }
        }
    });

    /** ======================================================
     * 11. Hitung subtotal otomatis
     * ====================================================== */
    function calculateSubtotals() {
        let totalDebit = 0, totalCredit = 0;

        $('#detail-rows tr').each(function() {
            const debit  = parseRp($(this).find('.debit-input').val());
            const credit = parseRp($(this).find('.credit-input').val());

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

    /** ======================================================
     * 12. Event listener input debit/kredit
     * ====================================================== */
    $(document).on('input', '.debit-input, .credit-input', function() {
        formatRpInput(this);

        // Pastikan hanya satu kolom terisi
        if ($(this).hasClass('debit-input')) {
            $(this).closest('tr').find('.credit-input').val('');
        } else {
            $(this).closest('tr').find('.debit-input').val('');
        }

        calculateSubtotals();
    });

    /** ======================================================
     * 13. Validasi submit form
     * ====================================================== */
    $('form').on('submit', function (e) {
        let totalDebit = 0, totalCredit = 0;

        $('#detail-rows tr').each(function() {
            totalDebit  += parseRp($(this).find('.debit-input').val());
            totalCredit += parseRp($(this).find('.credit-input').val());
        });

        if (totalDebit !== totalCredit) {
            e.preventDefault();
            alert('Transaksi tidak seimbang! Jumlah Debit dan Kredit harus sama.');
        } else {
            // Kirim angka mentah ke server (tanpa titik pemisah)
            $('.debit-input, .credit-input').each(function () {
                const v = $(this).val();
                if (v !== '') $(this).val(String(parseRp(v)));
            });
        }
    });
});
</script>
<script>
$('#transaction_date').on('change', function () {

    let selected = $(this).val();
    let licenseId = $('#license_id').val() ?? $('#activeLicenseId').val();

    if (!selected || !licenseId) return;

    $.get('/check-period', {
        date: selected,
        license_id: licenseId
    }, function(res) {

        if(res.closed){
            $('#period-warning').removeClass('d-none');
            $('button[type="submit"]').prop('disabled', true);
        }else{
            $('#period-warning').addClass('d-none');
            $('button[type="submit"]').prop('disabled', false);
        }

    });

});
</script>
@endpush