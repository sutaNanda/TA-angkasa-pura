@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div>
            <img class="mx-auto h-12 w-auto" src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Angkasa_Pura_II_logo_2017.svg/2560px-Angkasa_Pura_II_logo_2017.svg.png" alt="Angkasa Pura II">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Pendaftaran Karyawan
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Gunakan email internal perusahaan untuk mendaftar.
            </p>
        </div>
        <form class="mt-8 space-y-6" action="{{ route('register.post') }}" method="POST">
            @csrf
            
            @if(session('error'))
                <div class="bg-red-50 text-red-500 text-sm p-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="name" class="sr-only">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Nama Lengkap" value="{{ old('name') }}">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label for="email" class="sr-only">Email Perusahaan</label>
                    <input id="email" name="email" type="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Email (@angkasapura.co.id)" value="{{ old('email') }}">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label for="division_id" class="sr-only">Divisi / Unit</label>
                    <select id="division_id" name="division_id" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        <option value="" disabled selected>Pilih Divisi</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                        @endforeach
                    </select>
                    @error('division_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Password (Min. 8 Karakter)">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="sr-only">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Konfirmasi Password">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fa-solid fa-user-plus text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Daftar Sekarang
                </button>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 text-sm">
                    Sudah punya akun? Login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
