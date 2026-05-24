<section>
    <h2 class="text-base font-semibold text-gray-900 mb-1">Личные данные</h2>
    <p class="text-sm text-gray-500 mb-5">Имя, фамилия, телефон и email.</p>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Имя</label>
                <input id="name" name="name" type="text"
                    value="{{ old('name', $user->name) }}"
                    required autocomplete="name"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="lastname">Фамилия</label>
                <input id="lastname" name="lastname" type="text"
                    value="{{ old('lastname', $user->lastname) }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                @error('lastname')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="tel">Телефон</label>
            <input id="tel" name="tel" type="tel"
                value="{{ old('tel', $user->tel) }}"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                placeholder="+7 900 000 00 00">
            @error('tel')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email</label>
            <input id="email" name="email" type="email"
                value="{{ old('email', $user->email) }}"
                required autocomplete="username"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="bg-[#4A40E0] text-white rounded-xl px-6 py-2.5 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                Сохранить
            </button>
            @if(session('status') === 'profile-updated')
                <p x-data="{show:true}" x-show="show" x-transition x-init="setTimeout(()=>show=false,2500)"
                   class="text-sm text-emerald-600 font-medium">Сохранено ✓</p>
            @endif
        </div>
    </form>
</section>
