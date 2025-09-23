<script setup>
import {Link, usePage} from '@inertiajs/vue3'
import {ref, computed} from 'vue'

const page = usePage()

const canLogin = computed(() => page.props.canLogin)
const canRegister = computed(() => page.props.canRegister)
const isAuthed = computed(() => !!page.props.auth?.user)

const mobileMenuOpen = ref(false)
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <header class="border-b bg-white shadow-sm">
            <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
                <!-- Лого -->
                <Link class="font-bold text-2xl text-indigo-600 tracking-wide" href="/">
                    ProgrammPedia
                </Link>

                <!-- Навигация для десктопа -->
                <nav class="hidden md:flex gap-8 text-gray-700 font-medium">
                    <Link class="hover:text-indigo-600 transition" href="/">Главная</Link>
                    <Link :href="route?.('search') ?? '/search'" class="hover:text-indigo-600 transition">Поиск</Link>
                    <Link class="hover:text-indigo-600 transition" href="/about">О проекте</Link>
                </nav>

                <!-- Правая часть (кнопки) -->
                <div class="flex items-center gap-3">
                    <!-- Кнопка Документации (видна всегда) -->
                    <Link
                        :href="route?.('dashboard') ?? '/dashboard'"
                        class="hidden md:inline-block rounded-lg px-4 py-2 bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-900 transition"
                    >
                        Документации
                    </Link>

                    <!-- Кнопки login/register -->
                    <nav v-if="canLogin" class="hidden md:flex items-center gap-3">
                        <Link
                            v-if="isAuthed"
                            :href="route?.('dashboard') ?? '/dashboard'"
                            class="rounded-lg px-4 py-2 bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-900 transition"
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
                        </template>
                    </nav>

                    <!-- Кнопка гамбургера -->
                    <button
                        class="md:hidden p-2 rounded-md text-gray-700 hover:bg-gray-100"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <!-- иконка -->
                        <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Мобильное меню -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t bg-white px-6 py-4 space-y-4">
                <Link class="block hover:text-indigo-600 transition" href="/">Главная</Link>
                <Link :href="route?.('search') ?? '/search'" class="block hover:text-indigo-600 transition">Поиск</Link>
                <Link class="block hover:text-indigo-600 transition" href="/about">О проекте</Link>

                <Link
                    :href="route?.('dashboard') ?? '/dashboard'"
                    class="block rounded-lg px-4 py-2 bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-900 transition"
                >
                    Документации
                </Link>

                <div v-if="canLogin" class="space-y-2">
                    <Link
                        v-if="isAuthed"
                        :href="route?.('dashboard') ?? '/dashboard'"
                        class="block rounded-lg px-4 py-2 bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-900 transition"
                    >
                        Дом
                    </Link>
                    <template v-else>
                        <Link
                            :href="route?.('login') ?? '/login'"
                            class="block rounded-lg px-5 py-2 bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-500 transition"
                        >
                            Войти
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot/>
        </main>

        <footer class="border-t bg-gray-50">
            <div class="max-w-7xl mx-auto px-6 py-6 text-sm text-gray-600">
                © {{ new Date().getFullYear() }} ProgrammPedia
            </div>
        </footer>
    </div>
</template>
