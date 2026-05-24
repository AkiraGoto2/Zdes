<section>
    <h2 class="text-base font-semibold text-gray-900 mb-1">Пароль</h2>
    <p class="text-sm text-gray-500 mb-5">Используйте надёжный пароль из букв, цифр и символов.</p>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="update_password_current_password">Текущий пароль</label>
            <input id="update_password_current_password" name="current_password" type="password"
                autocomplete="current-password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
            @if($errors->updatePassword->get('current_password'))
                <p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="update_password_password">Новый пароль</label>
            <input id="update_password_password" name="password" type="password"
                autocomplete="new-password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
            @if($errors->updatePassword->get('password'))
                <p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="update_password_password_confirmation">Подтвердите новый пароль</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
            @if($errors->updatePassword->get('password_confirmation'))
                <p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="bg-[#4A40E0] text-white rounded-xl px-6 py-2.5 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                Обновить пароль
            </button>
            @if(session('status') === 'password-updated')
                <p x-data="{show:true}" x-show="show" x-transition x-init="setTimeout(()=>show=false,2500)"
                   class="text-sm text-emerald-600 font-medium">Пароль обновлён ✓</p>
            @endif
        </div>
    </form>
</section>
