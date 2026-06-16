<x-app-layout>
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 space-y-6">

    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-[#4A40E0] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold">Настройки профиля</h1>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        @include('profile.partials.update-profile-information-form')
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        @include('profile.partials.update-password-form')
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        @include('profile.partials.delete-user-form')
    </div>

</div>
</x-app-layout>
