<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const canLogin = page.props.canLogin
const canRegister = page.props.canRegister
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <header class="border-b bg-white shadow-sm">
      <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
        <!-- Лого -->
        <Link href="/" class="font-bold text-2xl text-indigo-600 tracking-wide">
          ProgrammPedia
        </Link>

        <!-- Навигация -->
        <nav class="hidden md:flex gap-8 text-gray-700 font-medium">
          <Link href="/" class="hover:text-indigo-600 transition">Главная</Link>
          <Link :href="route?.('search') ?? '/search'" class="hover:text-indigo-600 transition">Поиск</Link>
          <Link href="/about" class="hover:text-indigo-600 transition">О проекте</Link>
        </nav>

        <!-- Login/Register/Dashboard -->
        <nav v-if="canLogin" class="flex items-center gap-3">
          <Link
            v-if="page.props.auth?.user"
            :href="route?.('dashboard') ?? '/dashboard'"
            class="rounded-lg px-4 py-2 bg-gray-800 text-white text-sm font-semibold shadow hover:bg-gray-700 transition"
          >
            Дом
          </Link>

          <template v-else>
            <Link
              :href="route?.('login') ?? '/login'"
              class="rounded-lg px-5 py-2 bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-500 transition"
            >
              Войти
            </Link>

            <Link
              v-if="canRegister"
              :href="route?.('register') ?? '/register'"
              class="rounded-lg px-5 py-2 border border-indigo-600 text-indigo-600 text-sm font-semibold hover:bg-indigo-50 transition"
            >
              Регистрация
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <main class="flex-1">
      <slot />
    </main>

    <footer class="border-t bg-gray-50">
      <div class="max-w-7xl mx-auto px-6 py-6 text-sm text-gray-600">
        © {{ new Date().getFullYear() }} ProgrammPedia
      </div>
    </footer>
  </div>
</template>
