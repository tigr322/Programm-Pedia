<!-- resources/js/Pages/About.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({ layout: AppLayout })

const faqs = ref([
  {
    q: 'Что такое ProgrammPedia?',
    a: 'Это база знаний по разработке: проблемы ↔ решения, быстрый поиск по всему сайту (Laravel Scout + Meilisearch), вложения PDF и коллекции.',
    open: true,
  },
  {
    q: 'Как работает поиск?',
    a: 'На бэке Laravel Scout индексирует модели Problem и Solution в Meilisearch. На фронте — мгновенный запрос с подсветкой и пагинацией.',
    open: false,
  },
  {
    q: 'Можно ли добавлять свои решения?',
    a: 'Да. Авторизуйся и добавляй. Файлы PDF прикрепляются и доступны для скачивания прямо со страницы проблемы/решения.',
    open: false,
  },
])

function toggle(i) { faqs.value[i].open = !faqs.value[i].open }
</script>

<template>
  <div class="max-w-6xl mx-auto px-6 py-10 space-y-8">
    <!-- HERO -->
    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-8">
      <p class="text-sm tracking-wide text-indigo-600 font-semibold mb-2">О проекте</p>
      <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">
        ProgrammPedia — твой быстрый справочник по разработке
      </h1>
      <p class="mt-3 text-gray-600 max-w-3xl">
        Здесь мы собираем <span class="font-medium">проблемы</span> и
        <span class="font-medium">решения</span>, чтобы экономить время на рутине и
        быстрее двигаться к результату. Поиск по всей базе — за доли секунды.
      </p>

      <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <Link
          :href="route?.('search') ?? '/search'"
          class="inline-flex items-center justify-center px-5 py-3 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-900 transition shadow"
        >
          🔎 Открыть поиск
        </Link>
        <Link
          :href="route?.('dashboard') ?? '/dashboard'"
          class="inline-flex items-center justify-center px-5 py-3 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 font-semibold transition"
        >
          ➕ Добавить проблему
        </Link>
      </div>
    </section>

    <!-- ЧТО ВНУТРИ -->
    <section class="grid md:grid-cols-3 gap-6">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-bold text-indigo-700">⚡ Молниеносный поиск</h3>
        <p class="mt-2 text-gray-600">
          Laravel Scout + Meilisearch. Фильтры, сортировки и релевантность — из коробки.
        </p>
      </div>
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-bold text-indigo-700">🧩 Проблемы и решения</h3>
        <p class="mt-2 text-gray-600">
          Структура «вопрос → несколько решений», якоря на конкретное решение и комментарии.
        </p>
      </div>
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-bold text-indigo-700">📄 PDF-вложения</h3>
        <p class="mt-2 text-gray-600">
          Прикрепляй инструкции и чек-листы. Скачивание в один клик.
        </p>
      </div>
    </section>

    <!-- ТЕХСТЕК -->
    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
      <h2 class="text-xl font-bold text-gray-900">🛠 Технологии</h2>
      <div class="mt-4 flex flex-wrap gap-2">
        <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">Laravel 12</span>
        <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">PHP 8.3</span>
        <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">Vue 3 + Inertia</span>
        <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">TailwindCSS</span>
        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-sm">Meilisearch</span>
        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-sm">Laravel Scout</span>
        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-sm">MySQL</span>
        <span class="px-3 py-1 rounded-full bg-slate-50 text-slate-700 text-sm">Docker (dev)</span>
      </div>
    </section>

    <!-- КАК ЭТО РАБОТАЕТ -->
    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
      <h2 class="text-xl font-bold text-gray-900">🔧 Как это работает</h2>
      <ol class="mt-4 space-y-3">
        <li class="flex gap-3">
          <div class="mt-1 h-6 w-6 rounded-full bg-indigo-700 text-white flex items-center justify-center text-sm">1</div>
          <p class="text-gray-700">
            Добавляешь <span class="font-medium">проблему</span> через панель. Указываешь заголовок, описание и теги.
          </p>
        </li>
        <li class="flex gap-3">
          <div class="mt-1 h-6 w-6 rounded-full bg-indigo-700 text-white flex items-center justify-center text-sm">2</div>
          <p class="text-gray-700">
            Прикрепляешь одно или несколько <span class="font-medium">решений</span>, при необходимости — PDF.
          </p>
        </li>
        <li class="flex gap-3">
          <div class="mt-1 h-6 w-6 rounded-full bg-indigo-700 text-white flex items-center justify-center text-sm">3</div>
          <p class="text-gray-700">
            Scout индексирует запись в Meilisearch — поиск становится доступным сразу.
          </p>
        </li>
        <li class="flex gap-3">
          <div class="mt-1 h-6 w-6 rounded-full bg-indigo-700 text-white flex items-center justify-center text-sm">4</div>
          <p class="text-gray-700">
            На странице проблемы удобно просматриваешь решения, редактируешь и скачиваешь PDF.
          </p>
        </li>
      </ol>
    </section>

    <!-- ROADMAP -->
    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
      <h2 class="text-xl font-bold text-gray-900">🗺 Roadmap</h2>
      <ul class="mt-3 space-y-2 text-gray-700">
        <li>✅ Базовая структура «Problem → Solutions», загрузка PDF</li>
        <li>✅ Поиск (Scout + Meilisearch), страница Search</li>
        <li>⬜ Теги/Коллекции и фильтры по ним</li>
        <li>⬜ Оценка решений и сортировка по полезности</li>
        <li>⬜ Экспорт/импорт подборок</li>
      </ul>
    </section>

    <!-- FAQ -->
    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
      <h2 class="text-xl font-bold text-gray-900">❓ Частые вопросы</h2>
      <div class="mt-4 divide-y">
        <div
          v-for="(f, i) in faqs"
          :key="i"
          class="py-3"
        >
          <button
            class="w-full flex items-center justify-between text-left"
            @click="toggle(i)"
          >
            <span class="font-semibold text-gray-900">{{ f.q }}</span>
            <span class="text-gray-400">{{ f.open ? '−' : '+' }}</span>
          </button>
          <p v-if="f.open" class="mt-2 text-gray-600">{{ f.a }}</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h3 class="text-lg font-bold text-gray-900">Готов добавить свою задачу?</h3>
          <p class="text-gray-600">Чем больше базу наполняем — тем быстрее находим решения.</p>
        </div>
        <div class="flex gap-3">
          <Link
            :href="route?.('dashboard') ?? '/dashboard'"
            class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-900 transition shadow"
          >
            Добавить проблему
          </Link>
          <Link
            :href="route?.('search') ?? '/search'"
            class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 font-semibold transition"
          >
            🔎 Найти решение
          </Link>
        </div>
      </div>
    </section>
  </div>
</template>
