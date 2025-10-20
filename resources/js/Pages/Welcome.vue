<script setup>
import {Link} from '@inertiajs/vue3'
</script>

<template>
    <div class="relative min-h-screen bg-gradient-to-b from-indigo-50 via-white to-white flex flex-col">
        <!-- Decorative background -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute left-1/2 top-[-8rem] -translate-x-1/2 w-[72rem] h-[36rem] bg-gradient-to-tr from-indigo-200 via-purple-200 to-pink-200 opacity-40 blur-3xl"></div>
            <div class="absolute -right-24 bottom-[-10rem] w-[40rem] h-[40rem] rounded-full bg-gradient-to-br from-indigo-100 to-sky-100 opacity-60 blur-2xl"></div>
        </div>

        <!-- Navbar -->
        <header class="relative z-10">
            <div class="container mx-auto px-4 py-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold shadow-sm">P</span>
                    <span class="text-lg font-semibold tracking-tight text-gray-900">ProgrammPedia</span>
                </div>
                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <Link href="/search" class="text-gray-700 hover:text-gray-900">Поиск</Link>
                    <Link href="/about" class="text-gray-700 hover:text-gray-900">О проекте</Link>
                    <a href="https://github.com" target="_blank" class="text-gray-700 hover:text-gray-900">GitHub</a>
                </nav>
                <div class="md:hidden"></div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative z-10 flex-1 flex items-center">
            <div class="container mx-auto px-4 py-16 md:py-24 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gray-900 mb-6">
                        База знаний для разработчиков <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">Laravel</span>
                    </h1>
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        Практические инструкции, разборы ошибок и готовые сниппеты. Быстро находите проверенные решения и запускайте код без лишней теории.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <Link
                            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-indigo-600 text-white font-medium shadow-lg shadow-indigo-200 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                            href="/search"
                        >
                            🔎 Начать поиск
                        </Link>
                        <Link
                            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-gray-900 font-medium border border-gray-200 hover:border-gray-300 hover:bg-gray-50 shadow-sm transition"
                            href="/about"
                        >
                            ℹ️ Узнать больше
                        </Link>
                    </div>

                    <!-- Stats -->
                    <div class="mt-10 grid grid-cols-3 gap-6 max-w-md">
                        <div>
                            <div class="text-2xl font-bold text-gray-900">200+</div>
                            <div class="text-sm text-gray-500">готовых решений</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">11/12</div>
                            <div class="text-sm text-gray-500">поддержка версий</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">24/7</div>
                            <div class="text-sm text-gray-500">обновления базы</div>
                        </div>
                    </div>
                </div>

                <!-- Right preview card -->
                <div class="relative">
                    <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-indigo-200 via-purple-200 to-pink-200 opacity-60 blur-2xl"></div>
                    <div class="relative rounded-2xl border border-gray-100 bg-white/70 backdrop-blur p-6 shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-yellow-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-green-400"></span>
                            </div>
                            <span class="text-xs text-gray-500">snippet.php</span>
                        </div>
                        <pre class="text-sm leading-relaxed text-gray-800 bg-gray-50 rounded-lg p-4 overflow-x-auto"><code>// Пример: валидация FormRequest
public function rules(): array {
    return [
        'email' => ['required', 'email', 'max:255'],
        'password' => ['required', 'min:8'],
    ];
}

// Быстрый поиск ошибки:
// "Integrity constraint violation" → migrations/indexes</code></pre>
                        <div class="mt-4 text-xs text-gray-500">Готов к копированию и запуску</div>
                    </div>
                    <div class="absolute -bottom-6 -left-6 bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg shadow">
                        🚀 Laravel · Vue · Docker
                    </div>
                </div>
            </div>
        </section>

        <!-- Curated blocks -->
        <section class="relative z-10 py-12 md:py-16">
            <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div class="group p-6 rounded-2xl border border-gray-100 bg-white/70 backdrop-blur hover:shadow-xl transition">
                    <div class="mb-3 text-2xl">🧠</div>
                    <h3 class="font-semibold text-lg mb-2">Промпты для ИИ</h3>
                    <p class="text-gray-600 mb-4">Готовые подсказки для генерации кода, ревью и фикса ошибок.</p>
                    <Link href="/prompts" class="text-indigo-600 hover:text-indigo-700 font-medium">Смотреть →</Link>
                </div>
                <div class="group p-6 rounded-2xl border border-gray-100 bg-white/70 backdrop-blur hover:shadow-xl transition">
                    <div class="mb-3 text-2xl">🧰</div>
                    <h3 class="font-semibold text-lg mb-2">Помощники разработчика</h3>
                    <p class="text-gray-600 mb-4">Чеклисты, шаблоны коммитов, .editorconfig, линтеры.</p>
                    <Link href="/helpers" class="text-indigo-600 hover:text-indigo-700 font-medium">Открыть →</Link>
                </div>
                <div class="group p-6 rounded-2xl border border-gray-100 bg-white/70 backdrop-blur hover:shadow-xl transition">
                    <div class="mb-3 text-2xl">🔌</div>
                    <h3 class="font-semibold text-lg mb-2">Плагины и интеграции</h3>
                    <p class="text-gray-600 mb-4">Полезные пакеты для Laravel, готовые конфиги и примеры.</p>
                    <Link href="/plugins" class="text-indigo-600 hover:text-indigo-700 font-medium">Подробнее →</Link>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="relative z-10 bg-gray-50/80 py-16">
            <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition">
                    <h3 class="font-bold text-lg mb-2">🔎 Умный поиск</h3>
                    <p class="text-gray-600">Находит нужное решение по ошибке или ключевым словам, с подсказками и синонимами.</p>
                </div>
                <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition">
                    <h3 class="font-bold text-lg mb-2">📚 Реальные примеры</h3>
                    <p class="text-gray-600">Решения из практики: код, миграции, конфиги. Минимум теории — максимум пользы.</p>
                </div>
                <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition">
                    <h3 class="font-bold text-lg mb-2">⚡ Быстрый старт</h3>
                    <p class="text-gray-600">Просто копируй и запускай. Всё протестировано на Laravel 11/12.</p>
                </div>
            </div>
        </section>

     </div>
 </template>
