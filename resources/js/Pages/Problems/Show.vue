<!-- resources/js/Pages/Problems/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { onMounted, reactive, ref, watchEffect,onBeforeUnmount } from 'vue'
const previewRefs = reactive({}) // per-solution: ref на блок превью
const isFullscreen = ref(false)

function setPreviewRef(id) {
    // вернём функцию для v-bind:ref
    return (el) => { previewRefs[id] = { value: el } }
}

async function enterFullscreen(solId) {
    const el = previewRefs[solId]?.value
    if (!el) return
    if (el.requestFullscreen) {
        await el.requestFullscreen()
    } else if (el.webkitRequestFullscreen) {
        el.webkitRequestFullscreen()
    }
}

async function exitFullscreen() {
    if (document.fullscreenElement) {
        await document.exitFullscreen()
    } else if (document.webkitFullscreenElement) {
        document.webkitExitFullscreen()
    }
}

function onFsChange() {
    isFullscreen.value = !!document.fullscreenElement
}

onMounted(() => {
    document.addEventListener('fullscreenchange', onFsChange)
})
onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', onFsChange)
})
defineOptions({ layout: AppLayout })

const props = defineProps({
    problem: { type: Object, required: true },            // с relation solutions
    selectedSolutionId: { type: [Number, String, null], default: null },
    canEdit: { type: Boolean, default: false },
})

/* UI состояние */
const openSolutions = ref(true)
const viewMode = reactive({})   // per-solution: 'preview' | 'editor'
const forms = reactive({})      // per-solution useForm({ content })

// Инициализация форм/режимов
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

function downloadPdf(sol) {
    const href = route ? route('solutions.download', sol.id) : `/solutions/${sol.id}/download`
    window.open(href, '_blank')
}

onMounted(() => {
    // Скролл к выбранному решению из урла или к selectedSolutionId
    let targetId = null
    if (location.hash?.startsWith('#solution-')) {
        targetId = location.hash.replace('#solution-', '')
    } else if (props.selectedSolutionId) {
        targetId = String(props.selectedSolutionId)
    }
    if (targetId) {
        const el = document.getElementById(`solution-${targetId}`)
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
})
</script>
<script>

async function openFloatingWindow(sol) {
    const html = `
    <style>
      html,body{margin:0;height:100%;font-family:ui-sans-serif,system-ui,Segoe UI,Roboto,Arial}
      .wrap{height:100%;display:flex;flex-direction:column}
      header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e5e7eb}
      header h1{font-size:14px;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      main{flex:1;overflow:auto;padding:16px}
      .content{max-width:980px;margin:0 auto}
      .content *{max-width:100%}
      button{border:1px solid #d1d5db;background:#fff;border-radius:8px;padding:6px 10px;cursor:pointer}
    </style>
    <div class="wrap">
      <header>
        <h1>${(sol.title ?? 'Решение #' + sol.id).toString().replace(/</g,'&lt;')}</h1>
        <div>
          <button id="zoomIn">+</button>
          <button id="zoomOut">−</button>
          <button id="closeBtn">Закрыть</button>
        </div>
      </header>
      <main>
        <div class="content ql-editor">${sol.content ?? ''}</div>
      </main>
    </div>
    <script>
      (function(){
        let scale=1
        const content=document.querySelector('.content')
        document.getElementById('zoomIn').onclick=()=>{ scale=Math.min(2, scale+0.1); content.style.zoom=scale }
        document.getElementById('zoomOut').onclick=()=>{ scale=Math.max(0.6, scale-0.1); content.style.zoom=scale }
        document.getElementById('closeBtn').onclick=()=>{ window.close() }
      })()
    <\/script>
  `

    // 1) Пытаемся Document Picture-in-Picture
    // @ts-ignore
    const hasDocPiP = 'documentPictureInPicture' in window && window.documentPictureInPicture?.requestWindow
    if (hasDocPiP) {
        try {
            // @ts-ignore
            const pipWin = await window.documentPictureInPicture.requestWindow({ width: 1000, height: 700 })
            pipWin.document.write(html)
            pipWin.document.close()
            return
        } catch (e) {
            console.warn('DocPiP error', e)
        }
    }

    // 2) Фоллбек: отдельное окно
    const win = window.open('', '', 'width=1100,height=800,menubar=no,toolbar=no,location=no,status=no')
    if (win) {
        win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${(sol.title ?? 'Решение #' + sol.id).toString().replace(/</g,'&lt;')}</title></head><body>${html}</body></html>`)
        win.document.close()
    }
}
</script>
<template>
    <div class="max-w-5xl mx-auto p-6 space-y-6">
        <!-- Хлебные крошки -->
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

        <!-- Решения -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">
                    🧩 Решения
                    <span v-if="problem.solutions?.length" class="text-gray-500 text-base">
            ({{ problem.solutions.length }})
          </span>
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
                    :class="{
            'ring-2 ring-indigo-500': +selectedSolutionId === +sol.id
          }"
                >
                    <div class="text-sm text-gray-500">
                        {{ sol.slug ?? 'Без слага' }}
                    </div>
                    <h3 class="text-lg font-semibold text-indigo-700">
                        Название: {{ sol.title ?? 'Без названия' }}
                    </h3>

                    <!-- Кнопки режима + Скачать PDF -->
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
                    <div class="flex items-center gap-2 mb-2">
                        <button class="px-3 py-1 text-sm rounded border" @click="enterFullscreen(sol.id)">⛶ На весь экран</button>
                        <button v-if="isFullscreen" class="px-3 py-1 text-sm rounded border" @click="exitFullscreen()">Выйти</button>
                    </div>
                    <button class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                            @click="openFloatingWindow(sol)">
                        Открыть в отдельном окне
                    </button>

                    <div v-if="viewMode[String(sol.id)] === 'preview'" class="rounded-md border bg-white p-3">
                        <div
                            class="prose max-w-none ql-editor max-h-[700px] overflow-auto pr-3 custom-scroll"
                            v-html="sol.content"
                        ></div>
                        <p v-if="!sol.content" class="text-sm text-gray-500">Содержимое отсутствует.</p>
                    </div>

                    <!-- Редактор -->
                    <div v-else class="rounded-md border bg-white p-3 space-y-3">
                        <textarea
                            v-model="forms[String(sol.id)].content"
                            class="w-full min-h-[180px] p-3 border rounded-md font-mono text-sm"
                            :readonly="!canEdit"
                        ></textarea>
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
