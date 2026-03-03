<script setup>
import { inject, onMounted, ref, computed } from 'vue'
import draggable from 'vuedraggable'
import RichTextEditor from '../../Components/Editor/RichTextEditor.vue'
import QuizEditor from './QuizEditor.vue'

const notify = inject('notify')

const props = defineProps({
  courseId: { type: [Number, String], required: true },
})

const loading = ref(true)
const book = ref(null)
const chapters = ref([])

const selectedChapterId = ref(null)
const chapter = ref(null)
const tab = ref('content')

const chapterIds = computed(() => chapters.value.map(c => c.id))

const newChapterTitle = ref('')

const loadBook = async () => {
  loading.value = true
  const { data } = await axios.get(`/books/api/course/${props.courseId}/book`)
  book.value = data.book
  chapters.value = data.chapters
  loading.value = false
}

const loadChapter = async (id) => {
  const { data } = await axios.get(`/books/api/chapters/${id}`)
  chapter.value = {
    ...data.chapter,
    // backend stores json as string; parse if needed
    content_json: (() => {
      try { return data.chapter.content_json ? JSON.parse(data.chapter.content_json) : null } catch { return null }
    })(),
  }
}

const createChapter = async () => {
  if (!newChapterTitle.value) return
  const { data } = await axios.post(`/books/api/book/${book.value.id}/chapters/create`, {
    title: newChapterTitle.value,
  })
  if (data.success) {
    notify({ type: 'success', message: 'Capitolo creato' })
    newChapterTitle.value = ''
    await loadBook()
  }
}

const saveChapter = async (silent = false) => {
  const payload = {
    title: chapter.value.title,
    content_json: chapter.value.content_json,
    content_html: chapter.value.content_html,
    status: chapter.value.status,
  }
  const { data } = await axios.post(`/books/api/chapters/${chapter.value.id}/update`, payload)
  if (data.success && !silent) {
    notify({ type: 'success', message: 'Salvato' })
  }
  if (data.success) {
    await loadBook()
  }
}

let autosaveTimer = null
const scheduleAutosave = () => {
  if (!chapter.value) return
  clearTimeout(autosaveTimer)
  autosaveTimer = setTimeout(() => saveChapter(true), 1200)
}

const onReorder = async () => {
  const res = await axios.post(`/books/api/book/${book.value.id}/chapters/reorder`, {
    ids: chapterIds.value,
  })
  if (res.data.success) {
    notify({ type: 'success', message: 'Ordine capitoli aggiornato' })
    await loadBook()
  }
}

onMounted(loadBook)
</script>

<template>
  <div v-if="loading">Caricamento...</div>
  <div v-else>
    <v-row>
      <v-col cols="12" md="4">
        <div class="d-flex align-center mb-3">
          <h3>Capitoli</h3>
          <v-spacer />
        </div>

        <v-text-field v-model="newChapterTitle" label="Titolo nuovo capitolo" />
        <v-btn class="mb-4" @click="createChapter">Crea capitolo</v-btn>

        <draggable
          v-model="chapters"
          item-key="id"
          handle=".drag-handle"
          @end="onReorder"
        >
          <template #item="{ element: c }">
            <v-list>
              <v-list-item
                :title="c.title"
                :subtitle="c.status"
                @click="selectedChapterId = c.id; loadChapter(c.id)"
              >
                <template #prepend>
                  <v-icon class="drag-handle" style="cursor: grab">mdi-drag</v-icon>
                </template>
              </v-list-item>
            </v-list>
          </template>
        </draggable>
      </v-col>

      <v-col cols="12" md="8">
        <div v-if="!chapter" class="text-medium-emphasis">Seleziona un capitolo per modificarlo.</div>

        <div v-else>
          <v-tabs v-model="tab" class="mb-3">
            <v-tab value="content" prepend-icon="mdi-file-document-edit">Contenuto</v-tab>
            <v-tab value="quiz" prepend-icon="mdi-help-circle">Quiz</v-tab>
          </v-tabs>

          <v-tabs-window v-model="tab">
            <v-tabs-window-item value="content">
              <div class="d-flex align-center mb-3">
                <h3 class="mr-3">Editor capitolo</h3>
            <v-spacer />
            <v-btn variant="tonal" color="green" @click="saveChapter">Salva</v-btn>
          </div>

          <v-text-field v-model="chapter.title" label="Titolo" @update:model-value="scheduleAutosave" />

          <RichTextEditor
            v-model="chapter.content_json"
            :course-id="props.courseId"
            @update:html="(html) => { chapter.content_html = html; scheduleAutosave(); }"
            @update:modelValue="() => scheduleAutosave()"
          />

          <v-row class="mt-3">
            <v-col cols="12" md="4">
              <v-select
                v-model="chapter.status"
                :items="['draft','published']"
                label="Stato"
                @update:model-value="scheduleAutosave"
              />
            </v-col>
          </v-row>
            </v-tabs-window-item>
            <v-tabs-window-item value="quiz">
              <QuizEditor :chapter-id="chapter.id" />
            </v-tabs-window-item>
          </v-tabs-window>
        </div>
      </v-col>
    </v-row>
  </div>
</template>
