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
                                            @if(isset($journal) && $journal->details)
                                                @foreach ($journal->details as $i => $detail)
                                                        @php $rp = fn($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ','); @endphp
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
                                                                    data-selected="{{ $detail->person ?? '' }}"
                                                                    data-selected-name="{{ $detail->person ? $detail->person_name : '' }}">
                                                                <option value="">-- Pilih User --</option>
                                                                @if($detail->person)
                                                                    {{-- Tampil langsung dari server; JS mengganti dengan daftar lengkap setelah AJAX selesai --}}
                                                                    <option value="{{ $detail->person }}" selected>{{ $detail->person_name }}</option>
                                                                @endif
                                                                {{-- Opsi user diisi oleh JS (renderUserOptions) sesuai person_type akun --}}
                                                            </select>
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
                                                            <input type="text" inputmode="decimal" autocomplete="off" 
                                                                name="details[{{ $i }}][debit]" 
                                                                class="form-control debit-input" 
                                                                value="{{ $rp(old("details.$i.debit", $detail->debit)) }}"
                                                                {{ $detail->credit ? 'disabled' : '' }}>

                                                            @if($detail->credit)
                                                                <input type="hidden" name="details[{{ $i }}][debit]" value="{{ $detail->debit }}">
                                                            @endif
                                                        </td>

                                                        
                                                        <td>
                                                            <input type="text" inputmode="decimal" autocomplete="off" 
                                                                name="details[{{ $i }}][credit]" 
                                                                class="form-control credit-input" 
                                                                value="{{ $rp(old("details.$i.credit", $detail->credit)) }}"
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

    calculateSubtotals(); // format subtotal saat halaman dibuka

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
    const userCache = {};
    const userUrlMap = {
        student: '/get-students',
        employee: '/get-employees',
        licenseholder: '/get-licenseholders',
        license: '/get-licenses'
    };

    /** Select2 user dengan fitur input manual (sama seperti form create) */
    function initUserSelect2($select) {
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.select2({
            placeholder: '-- Input manual jika tidak ada User --',
            width: '100%',
            tags: true,
            createTag: function (params) {
                const term = $.trim(params.term);
                if (term === '') return null;
                return { id: term, text: term, newOption: true };
            }
        });
    }

    function fillUserOptions($select, list, selected) {
        const hasSelected = selected !== null && selected !== undefined && selected !== '';
        let found = false;

        $select.empty().append('<option value="">-- Pilih User --</option>');

        $.each(list, function (_, user) {
            const isSelected = hasSelected && String(selected) === String(user.id);
            if (isSelected) found = true;
            $select.append($('<option>').val(user.id).text(user.name).prop('selected', isSelected));
        });

        // Nilai lama tidak ada di daftar (mis. user input manual) → tetap tampilkan
        if (hasSelected && !found) {
            const label = $select.attr('data-selected-name') || selected;
            $select.append($('<option>').val(selected).text(label).prop('selected', true));
        }

        initUserSelect2($select);
    }

    /** 🔹 Render user sesuai person_type */
    function renderUserOptions($select, personType, selected = null) {
        const url = userUrlMap[personType];

        if (!url) {
            fillUserOptions($select, [], selected);
            return;
        }

        if (userCache[personType]) {
            fillUserOptions($select, userCache[personType], selected);
            return;
        }

        $.get(url, function (data) {
            userCache[personType] = data;
            fillUserOptions($select, data, selected);
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

        if (selectedVal) {
            $(this).prop('disabled', false);
            $(this).after(
                `<input type="hidden" name="${$(this).attr('name')}" value="${selectedVal}">`
            );
        }
        // Select user TIDAK diberi hidden input: input hidden bernama sama akan
        // menimpa nilai select saat submit, sehingga user tidak bisa diubah.
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
        let selectedUser = $select.attr('data-selected'); // attr agar tidak dikonversi jadi angka
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
                <td><input type="text" inputmode="decimal" autocomplete="off" name="details[${rowCount}][debit]" class="form-control debit-input"></td>
                <td><input type="text" inputmode="decimal" autocomplete="off" name="details[${rowCount}][credit]" class="form-control credit-input"></td>
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

        // Siapkan select user (dengan input manual)
        initUserSelect2($('#detail-rows tr:last .user-select'));
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

    /** 🔹 Event listener input debit/kredit */
    $(document).on('input', '.debit-input, .credit-input', function() {
        formatRpInput(this);
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
            totalDebit  += parseRp($(this).find('.debit-input').val());
            totalCredit += parseRp($(this).find('.credit-input').val());
        });

        if (totalDebit !== totalCredit) {
            e.preventDefault();
            alert('Transaksi tidak balance! Jumlah Debit dan Kredit harus sama.');
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

@endpush