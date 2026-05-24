<section>
    <h2 class="text-base font-semibold text-red-600 mb-1">Удаление аккаунта</h2>
    <p class="text-sm text-gray-500 mb-5">
        После удаления аккаунта все данные будут безвозвратно стёрты — события, записи, фотографии.
    </p>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="border border-red-200 text-red-500 hover:bg-red-50 rounded-xl px-5 py-2.5 text-sm font-semibold transition-colors">
        Удалить аккаунт
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-gray-900 mb-1">Удалить аккаунт?</h2>
            <p class="text-sm text-gray-500 mb-5">
                Это действие нельзя отменить. Введите пароль для подтверждения.
            </p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
                <input id="password" name="password" type="password"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
                    placeholder="Введите пароль">
                @if($errors->userDeletion->get('password'))
                    <p class="text-xs text-red-500 mt-1">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                    class="border border-gray-200 text-gray-600 rounded-xl px-5 py-2 text-sm hover:bg-gray-50 transition-colors">
                    Отмена
                </button>
                <button type="submit"
                    class="bg-red-500 hover:bg-red-600 text-white rounded-xl px-5 py-2 text-sm font-semibold transition-colors">
                    Удалить навсегда
                </button>
            </div>
        </form>
    </x-modal>
</section>
