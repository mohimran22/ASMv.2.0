@extends('tablar::page')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <!-- Page pre-title -->
                    <div class="page-pretitle">
                        Overview
                    </div>
                    <p class="page-title">
                        Pengguna
                    </p>
                </div>
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                 
                        <a href=" {{ route("users.index") }} " class="btn btn-primary d-none d-sm-inline-block" >
                            Kembali
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="text-center mb-4" style="font-size: 1.4rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                Tambah Data Pengguna
                            </p>
                        </div>

                        <div class="card-body">
                            <form class="font-normal" action="{{ route('users.store') }}" method="POST">
                                @csrf

                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="name">Nama: *</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                

                                    <div class="col-md-6 mb-3">
                                        <label for="email">Email: *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="password">Password: *</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" value="{{ old('password') }}" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="role">Role: *</label>
                                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                            <option value="">-- Pilih Role --</option>
                                            @foreach ($roles as $roleName)
                                                <option value="{{ $roleName }}"
                                                    {{ old('role') == $roleName ? 'selected' : '' }}>
                                                    {{ ucfirst($roleName) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-4 text-white">Submit</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

