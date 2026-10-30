@php
    $buildingTypes      = [1 => 'Rukan', 2 => 'Residence', 3 => 'Ruko'];
    $buildingStatuses   = [1 => 'Milik', 2 => 'Sewa', 3 => 'Lain-Lain'];
    $buildingConditions = [1 => 'Baik', 2 => 'Cukup', 3 => 'Tidak Baik'];

    $statusColor = function ($status) {
        return match (strtolower((string) $status)) {
            'aktif', 'active'                   => 'green',
            'expired', 'kadaluarsa', 'nonaktif' => 'red',
            default                             => 'secondary',
        };
    };

    $socials = [
        'instagram'                         => ['Instagram', 'ti-brand-instagram'],
        'facebook_page'                     => ['Facebook', 'ti-brand-facebook'],
        'tiktok'                            => ['Tiktok', 'ti-brand-tiktok'],
        'youtube'                           => ['Youtube', 'ti-brand-youtube'],
        'google_maps'                       => ['Google Maps', 'ti-map-2'],
        'landing_page_student_registration' => ['Landing Page Pendaftaran', 'ti-link'],
    ];
@endphp

<style>
    .holder-licenses .section-icon {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        background: rgba(32, 107, 196, .1);
        color: #206bc4;
        flex-shrink: 0;
    }
    .holder-licenses .subheader-line {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--tblr-secondary, #667382);
        margin: 1.25rem 0 .75rem;
    }
    @media (max-width: 575.98px) {
        .holder-licenses .card-body { padding: 1rem; }
    }
</style>

<div class="holder-licenses">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="section-icon"><i class="ti ti-license"></i></span>
        <div>
            <h3 class="mb-0">Data Lisensi Pemilik</h3>
            <div class="text-muted small">{{ $license_holder->fullname }}</div>
        </div>
    </div>

    @if ($license_holder->user && $license_holder->user->licenses->count())
        @foreach ($license_holder->user->licenses as $license)
            <div class="card mb-3">
                {{-- Header: nama, tipe, status --}}
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center gap-2 w-100">
                        <div class="me-auto min-w-0">
                            <h4 class="card-title mb-0 text-break">{{ $license->name }}</h4>
                            <div class="text-muted small">ID Lisensi: {{ $license->license_id ?: '-' }}</div>
                        </div>
                        <span class="badge bg-blue-lt">{{ $license->license_type ?: '-' }}</span>
                        <span class="badge bg-{{ $statusColor($license->status) }}-lt text-{{ $statusColor($license->status) }}">
                            {{ ucfirst($license->status) ?: '-' }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Informasi lisensi --}}
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Email</div>
                            <div class="datagrid-content text-break">{{ $license->email ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Telepon</div>
                            <div class="datagrid-content">{{ $license->phone ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor Aqad</div>
                            <div class="datagrid-content text-break">{{ $license->contract_agreement_number ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Bergabung</div>
                            <div class="datagrid-content">{{ $license->join_date ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Expired</div>
                            <div class="datagrid-content">{{ $license->expired_date ?: '-' }}</div>
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div class="subheader-line">Alamat</div>
                    <div class="datagrid">
                        <div class="datagrid-item" style="grid-column: 1 / -1;">
                            <div class="datagrid-title">Alamat</div>
                            <div class="datagrid-content">{{ $license->address ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Provinsi</div>
                            <div class="datagrid-content">{{ $license->province->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kabupaten/Kota</div>
                            <div class="datagrid-content">{{ $license->city->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kecamatan</div>
                            <div class="datagrid-content">{{ $license->district->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kelurahan</div>
                            <div class="datagrid-content">{{ $license->subDistrict->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kode Pos</div>
                            <div class="datagrid-content">{{ $license->postalCode->postal_code ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- Bangunan --}}
                    <div class="subheader-line">Bangunan</div>
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tipe Bangunan</div>
                            <div class="datagrid-content">{{ $buildingTypes[$license->building_type] ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status Bangunan</div>
                            <div class="datagrid-content">{{ $buildingStatuses[$license->building_status] ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Expired Sewa</div>
                            <div class="datagrid-content">{{ $license->building_rent_expired_date ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Luas Bangunan</div>
                            <div class="datagrid-content">{{ $license->building_area ? $license->building_area . ' m²' : '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kondisi Bangunan</div>
                            <div class="datagrid-content">{{ $buildingConditions[$license->building_condition] ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Punya AC?</div>
                            <div class="datagrid-content">
                                @if ($license->building_has_ac === null || $license->building_has_ac === '')
                                    -
                                @else
                                    <span class="badge bg-{{ $license->building_has_ac ? 'green' : 'secondary' }}-lt">
                                        {{ $license->building_has_ac ? 'Ya' : 'Tidak' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Sosial media --}}
                    <div class="subheader-line">Sosial Media & Tautan</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($socials as $field => [$label, $icon])
                            @if ($license->$field)
                                <a href="{{ $license->$field }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                    <i class="ti {{ $icon }} me-1"></i> {{ $label }}
                                </a>
                            @else
                                <span class="btn btn-sm btn-outline-secondary disabled" aria-disabled="true">
                                    <i class="ti {{ $icon }} me-1"></i> {{ $label }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Aksi --}}
                @if (auth()->user()->can('pemilik-lisensi.ubah') || ! auth()->user()->hasRole('Pemilik Lisensi'))
                    <div class="card-footer">
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                            @if (auth()->user()->can('pemilik-lisensi.ubah'))
                                <a href="{{ route('licenses.edit', $license->id) }}" class="btn btn-warning" title="Ubah">
                                    <i class="ti ti-edit me-1"></i> Ubah
                                </a>
                            @endif

                            @unless (auth()->user()->hasRole('Pemilik Lisensi'))
                                <form action="{{ route('licenses.destroy', $license->id) }}" method="POST" class="d-grid d-sm-block" onsubmit="return confirmDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="ti ti-trash me-1"></i> Hapus
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="ti ti-license-off fs-1 d-block mb-2"></i>
                Belum ada data lisensi.
            </div>
        </div>
    @endif

    <div class="text-end mt-3">
        <a href="{{ route('license_holders.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke daftar
        </a>
    </div>
</div>