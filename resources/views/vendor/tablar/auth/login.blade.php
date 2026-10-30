@extends('tablar::auth.layout')
@section('title', 'Login')
@section('content')
    <div class="container container-tight py-4">
        
        <div class="text-center mb-1 mt-5">
            <a href="" class="navbar-brand navbar-brand-autodark">
                <img src="{{asset(config('tablar.auth_logo.img.path','assets/logo.svg'))}}" height="36"
                     alt="">
            </a>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <p class="text-center mb-4">
                    Selamat datang di AHA Right Brain
                </p>

                <form class="font-normal" action="{{route('login')}}" method="post" autocomplete="off" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label ">Email</label>
                        <input type="email" class="form-control font-normal italic-placeholder @error('email') is-invalid @enderror" name="email"
                               placeholder="emailkamu@email.com"
                               autocomplete="off">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">
                            Kata sandi
                        </label>

                        <div class="input-group input-group-flat">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Kata sandi Anda"
                                   autocomplete="off">

                            <span class="input-group-text">
                                <a href="#" id="togglePassword" class="link-secondary" title="Show password" aria-label="Show password">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="12" cy="12"r="2"/>
                                    <path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7"/>
                                </svg>

                                <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" class="icon" style="display: none;" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 3l18 18" />
                                    <path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" />
                                    <path d="M9.878 5.878c.995-.586 2.111-.878 3.122-.878 4 0 7.333 2.333 10 7 -1.193 2.089 -2.495 3.695 -3.907 4.82" />
                                    <path d="M6.14 6.128c-1.653 1.147 -3.041 2.797 -4.14 4.872 2.667 4.667 6 7 10 7 1.575 0 3.082 -.355 4.5 -1" />
                                </svg>
                                </a>
                            </span>
                  
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <script>
                        const passwordInput = document.getElementById('password');
                        const togglePassword = document.getElementById('togglePassword');
                        const eyeIcon = document.getElementById('eyeIcon');
                        const eyeOffIcon = document.getElementById('eyeOffIcon');

                        togglePassword.addEventListener('click', function (e) {
                            e.preventDefault();

                            const isPassword = passwordInput.type === 'password';
                            passwordInput.type = isPassword ? 'text' : 'password';

                            eyeIcon.style.display = isPassword ? 'none' : 'inline';
                            eyeOffIcon.style.display = isPassword ? 'inline' : 'none';

                            const title = isPassword ? 'Hide password' : 'Show password';
                            togglePassword.setAttribute('aria-label', title);
                            togglePassword.setAttribute('title', title);
                        });
                    </script>
                   
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Masuk</button>
                    </div>

                </form>
            </div>
            
        </div>
        <div class="text-center text-secondary mt-3 small">
            &copy; {{ date('Y') }} AHA Right Brain. Hak cipta dilindungi.
        </div>
    </div>
@endsection