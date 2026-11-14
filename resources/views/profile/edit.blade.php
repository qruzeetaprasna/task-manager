<x-app-layout>
    <div class="mx-auto max-w-[960px] space-y-6 px-6 py-8">
        <header>
            <h1 class="text-[28px] font-bold leading-tight text-gray-900">Profil Pengguna</h1>
            <p class="mt-1 text-sm text-gray-600">
                Perbarui informasi akun dan pengaturan keamananmu.
            </p>
        </header>

        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            @include('profile.partials.update-password-form')
        </section>

        <section class="rounded-xl border border-red-200 bg-white p-5 sm:p-6">
            @include('profile.partials.delete-user-form')
        </section>
    </div>
</x-app-layout>
