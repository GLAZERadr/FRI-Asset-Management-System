@extends('layouts.auth')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-center mb-6">Login</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <label for="username" class="block text-gray-700 mb-2">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required autofocus>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" 
                   class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" 
                   required>
        </div>

        <div class="flex items-center mb-4">
            <input type="checkbox" id="remember" name="remember" class="mr-2">
            <label for="remember" class="text-gray-600">Remember me</label>
        </div>

        <button type="submit" class="w-full py-2 px-4 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
            Log in
        </button>
    </form>

    <div class="mt-4 text-center">
        <p class="text-gray-600">
            Don't have an account? <a href="{{ route('register') }}" class="text-green-600 hover:underline">Register</a>
        </p>
    </div>
</div>
@endsection