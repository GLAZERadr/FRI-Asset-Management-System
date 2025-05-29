@extends('layouts.auth')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md mb-6 mt-6">
    <h2 class="text-2xl font-semibold text-center mb-6">Register</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-gray-700 mb-2">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required autofocus>
        </div>

        <div class="mb-4">
            <label for="username" class="block text-gray-700 mb-2">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required>
        </div>

        <div class="mb-4">
            <label for="division" class="block text-gray-700 mb-2">Division</label>
            <input type="text" id="division" name="division" value="{{ old('division') }}" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required>
        </div>

        <div class="mb-4">
            <label for="role" class="block text-gray-700 mb-2">Role</label>
            <select id="role" name="role" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                    required>
                <option value="">Select Role</option>
                <option value="staff_logistik" {{ old('role') == 'staff_logistik' ? 'selected' : '' }}>Staff Logistik</option>
                <option value="kaur_laboratorium" {{ old('role') == 'kaur_laboratorium' ? 'selected' : '' }}>Kaur Laboratorium</option>
                <option value="kaur_keuangan_logistik_sdm" {{ old('role') == 'kaur_keuangan_logistik_sdm' ? 'selected' : '' }}>Kaur Keuangan Logistik SDM</option>
                <option value="wakil_dekan_2" {{ old('role') == 'wakil_dekan_2' ? 'selected' : '' }}>Wakil Dekan 2</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required>
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 mb-2">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required>
        </div>

        <button type="submit" class="w-full py-2 px-4 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
            Register
        </button>
    </form>

    <div class="mt-4 text-center">
        <p class="text-gray-600">
            Already have an account? <a href="{{ route('login') }}" class="text-green-600 hover:underline">Log in</a>
        </p>
    </div>
</div>
@endsection