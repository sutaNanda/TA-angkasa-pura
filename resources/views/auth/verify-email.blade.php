@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg border-t-4 border-blue-500">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fa-solid fa-envelope text-blue-600"></i>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-900">Verifikasi Email Anda</h2>
            <p class="mt-2 text-sm text-gray-600">
                Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan ke email Anda.
            </p>
            @if (session('status') == 'verification-link-sent')
                <div class="mt-4 bg-green-50 text-green-700 text-sm p-3 rounded border border-green-200">
                    Link verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            @endif
        </div>

        <div class="mt-6 flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
