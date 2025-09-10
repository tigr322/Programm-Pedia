<!-- resources/js/Pages/Problems/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { onMounted, reactive, ref, watchEffect } from 'vue'



defineOptions({ layout: AppLayout })

const props = defineProps({
  problem: { type: Object, required: true }, // ожидаем problem с solutions
  canEdit: { type: Boolean, default: false }, // если true — можно сохранять решения
})

/* -------- Состояния UI -------- */
const openSolutions = ref(true) // показывать/скрывать список решений
const viewMode = reactive({})   // per-solution: 'preview' | 'editor'
const forms = reactive({})      // per-solution useForm({ content })

// Инициализируем формы/режимы для всех решений
watchEffect(() => {
  (props.problem?.solutions ?? []).forEach((s) => {
    const k = String(s.id)
    if (!forms[k]) {
      forms[k] = useForm({ content: s.content ?? '' })
    }
    if (!viewMode[k]) {
      viewMode[k] = 'preview'
    }
  })
})

// Сохранение решения (если canEdit)
function saveSolution(sol) {
  if (!props.canEdit) return
  const k = String(sol.id)
  forms[k].put(route('solutions.update', sol.id), {
    preserveScroll: true,
    onSuccess: () => {
      viewMode[k] = 'preview'
    },
  })
}

// Скачать PDF — просто открыть в новой вкладке
function downloadPdf(sol) {
  const href = route ? route('solutions.download', sol.id) : `/solutions/${sol.id}/download`
  window.open(href, '_blank')
}

// Скролл к якорю #solution-<id>
onMounted(() => {
  if (location.hash?.startsWith('#solution-')) {
    const id = location.hash.replace('#solution-', '')
    const el = document.getElementById(`solution-${id}`)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
})
</script>

<template>
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <!-- Навигация/хлебные крошки -->
    <div class="text-sm text-gray-500">
      <Link :href="route?.('dashboard') ?? '/dashboard'" class="underline hover:no-underline">Дом</Link>
      <span class="mx-2">/</span>
      <span>Проблема</span>
    </div>

    <!-- Карточка проблемы -->
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ problem.title || 'Без названия' }}</h1>
      <p class="text-sm text-gray-500 mt-1 break-all">/{{ problem.slug }}</p>

      <article class="prose max-w-none mt-4" v-html="problem.description"></article>
    </div>

    <!-- Блок решений -->
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">
          🧩 Решения
          <span v-if="problem.solutions?.length" class="text-gray-500 text-base">({{ problem.solutions.length }})</span>
        </h2>

        <button
          class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
          @click="openSolutions = !openSolutions"
        >
          {{ openSolutions ? 'Скрыть решения' : 'Показать решения' }}
        </button>
      </div>

      <div v-if="openSolutions" class="mt-4 space-y-4">
        <div
          v-for="sol in (problem.solutions || [])"
          :key="String(sol.id)"
          :id="`solution-${sol.id}`"
          class="border rounded-lg p-4 bg-gray-50 hover:shadow-md transition"
        >
          <!-- Кнопки режима и Скачать PDF -->
          <div class="flex items-center justify-between gap-3 mb-3">
            <div class="flex gap-2">
              <button
                class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                :class="{'opacity-60': viewMode[String(sol.id)] === 'preview'}"
                @click="viewMode[String(sol.id)] = 'preview'"
              >
                Просмотр
              </button>
              <button
                class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                :class="{'opacity-60': viewMode[String(sol.id)] === 'editor'}"
                @click="viewMode[String(sol.id)] = 'editor'"
              >
                Редактор
              </button>
            </div>

            <div class="flex gap-2 items-center">
              <button
                v-if="sol.pdf_path"
                class="px-3 py-2 text-sm rounded-lg bg-indigo-700 text-white hover:bg-indigo-900"
                @click="downloadPdf(sol)"
              >
                Скачать PDF
              </button>
              <span class="text-xs text-gray-500">
                {{ new Date(sol.created_at).toLocaleString() }}
              </span>
            </div>
          </div>

          <!-- Просмотр -->
          <div v-if="viewMode[String(sol.id)] === 'preview'" class="rounded-md border bg-white p-3">
            <div
    class="prose max-w-none ql-editor max-h-[700px] overflow-auto pr-3 custom-scroll"
    v-html="sol.content"
  ></div>
            <p v-if="!sol.content" class="text-sm text-gray-500">Содержимое отсутствует.</p>
          </div>

          <!-- Редактор (по умолчанию только чтение, можно включить canEdit) -->
          <div v-else class="rounded-md border bg-white p-3 space-y-3">
            <textarea
              v-model="forms[String(sol.id)].content"
              class="w-full min-h-[180px] p-3 border rounded-md font-mono text-sm"
              :readonly="!canEdit"
            />
            <div class="flex items-center justify-between">
              <div class="text-xs text-gray-500">
                ID: {{ sol.id }}
              </div>
              <button
                v-if="canEdit"
                class="px-4 py-2 text-sm rounded-lg bg-gray-800 text-white hover:bg-gray-700"
                :disabled="forms[String(sol.id)].processing"
                @click="saveSolution(sol)"
              >
                {{ forms[String(sol.id)].processing ? 'Сохранение…' : 'Сохранить' }}
              </button>
            </div>
          </div>

          <!-- Быстрые ссылки -->
          <div class="mt-3 text-xs text-gray-500 flex flex-wrap gap-3">
            <a :href="`#solution-${sol.id}`" class="underline hover:no-underline">Ссылка на это решение</a>
            <Link
              :href="route?.('problems.show', problem.slug) ?? `/problems/${encodeURIComponent(problem.slug)}`"
              class="underline hover:no-underline"
            >
              К началу проблемы
            </Link>
          </div>
        </div>

        <p v-if="!problem.solutions || !problem.solutions.length" class="text-sm text-gray-500">
          Пока нет решений.
        </p>
      </div>
    </div>
  </div>
</template>
