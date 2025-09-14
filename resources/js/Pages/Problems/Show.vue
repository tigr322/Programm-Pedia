<!-- resources/js/Pages/Problems/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import {Link, useForm, usePage} from '@inertiajs/vue3'
import {onMounted, reactive, ref, watchEffect, onBeforeUnmount, computed} from 'vue'
import openFloatingWindow from '@/windowsopen'
import {QuillEditor} from "@vueup/vue-quill";
import { router } from '@inertiajs/vue3'

const isFullscreen = ref(false)

const editForms = reactive({});
const openEditForm = reactive({});
const solutionForms = reactive({});

const openEdit = (sol, problemId) => {
    const k = String(sol.id);
    if (!editForms[k]) {
        editForms[k] = useForm({
            solution_id: sol.id,
            content: sol.content || '',
            pdf: null,
            title: sol.title ?? '',
            slug: sol.slug ?? '',
            summary: sol.summary ?? '',
            markdown: sol.markdown ?? '',
            code: sol.code ?? '',
            language: sol.language ?? 'plaintext',
        });
    }
    openEditForm[k] = !openEditForm[k];
};

const onEditFileChange = (solutionId, e) => {
    const k = String(solutionId);
    if (!editForms[k]) return;
    editForms[k].pdf = e.target.files?.[0] ?? null;
};

const submitEdit = (problemId, solutionId) => {
    const k = String(solutionId);
    editForms[k].post(route('solutions.store', { problem: problemId }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            openEditForm[k] = false;
            editForms[k].reset(); // очистит временный PDF; контент перезагрузится с сервера
        },
    });
};
const form = useForm({})


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
const page = usePage()
const isAuthed    = computed(() => !!page.props.auth?.user)
watchEffect(() => {
    (props.problem ?? []).forEach((p) => {
        if (!solutionForms[p.id]) {
            solutionForms[p.id] = useForm({slug: '', title: '', content: '', pdf: null });
        }
    });
});
const submitDelete = (solutionId) => {
    if (!confirm('Точно удалить решение?')) return

    form.delete(route('solutions.destroy', solutionId), {
        preserveScroll: true,
        onSuccess: () => {
            const i = props.problem.solutions.findIndex(s => +s.id === +solutionId)
            if (i !== -1) props.problem.solutions.splice(i, 1)
        },
    })
}
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
                        Название: {{  sol.title ?? 'Без названия' }}
                    </h3>



                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex gap-2">
                            <button
                                class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                                :class="{'opacity-60': viewMode[String(sol.id)] === 'preview'}"
                                @click="viewMode[String(sol.id)] = 'preview'"
                            >
                                Просмотр
                            </button>
                            <button   v-if="isAuthed"
                                      class="px-3 py-2 text-sm rounded-lg bg-indigo-700 text-white hover:bg-indigo-900"
                                      @click="openEdit(sol, problem.id)"
                            >
                                {{ openEditForm[String(sol.id)] ? 'Отменить' : 'Редактировать' }}
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
                            <button v-if="isAuthed"
                                    class="px-3 py-2 text-sm rounded-lg border bg-red-600 hover:bg-red-900 text-slate-100"
                                    @click="submitDelete(sol.id)"
                            >
                                Удалить
                            </button>
                            <span class="text-xs text-gray-500">
                                {{ new Date(sol.created_at).toLocaleString() }}
                              </span>
                        </div>
                    </div>
                    <div v-if="openEditForm[String(sol.id)]" class="mt-4 rounded-md border p-4 bg-white">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Редактировать решение</h4>
                        <input
                            v-model="sol.title"
                            type="text"
                            placeholder="Краткое название решения"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Текст решения</label>
                            <QuillEditor
                                v-model:content="editForms[String(sol.id)].content"
                                contentType="html"
                                theme="snow"
                                toolbar="full"
                                style="height: 240px;"
                            />
                            <div v-if="editForms[String(sol.id)].errors?.content" class="text-sm text-red-500">
                                {{ editForms[String(sol.id)].errors.content }}
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PDF (необязательно, заменит старый)</label>
                                <input
                                    type="file"
                                    accept="application/pdf"
                                    @change="onEditFileChange(sol.id, $event)"
                                    class="w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-700 file:px-3 file:py-2 file:text-white hover:file:bg-indigo-700"
                                />
                                <div v-if="editForms[String(sol.id)].errors?.pdf" class="text-sm text-red-500 mt-1">
                                    {{ editForms[String(sol.id)].errors.pdf }}
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button
                                    type="button"
                                    class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50"
                                    @click="openEditForm[String(sol.id)] = false"
                                >
                                    Отмена
                                </button>
                                <button
                                    type="button"
                                    class="px-4 py-2 bg-indigo-700 text-white rounded-lg hover:bg-indigo-900"
                                    :disabled="editForms[String(sol.id)].processing"
                                    @click="submitEdit(problem.id, sol.id)"
                                >
                                    Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Просмотр -->
                    <div class="flex items-center gap-2 mb-2">
                        <button v-if="isFullscreen" class="px-3 py-1 text-sm rounded border" @click="exitFullscreen()">Выйти</button>
                    </div>

                    <button class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                            @click="openFloatingWindow({ ...sol, problem: { slug: problem.slug } })">
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
