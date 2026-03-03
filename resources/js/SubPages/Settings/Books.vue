<script setup>
import { inject, onMounted, ref } from 'vue'
import RichTextEditor from '../../Components/Editor/RichTextEditor.vue'

const notify = inject('notify')

const props = defineProps({
  courseId: { type: [Number, String], required: true },
})

const loading = ref(true)
const book = ref(null)
const chapters = ref([])

const selectedChapterId = ref(null)
const chapter = ref(null)

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

const saveChapter = async () => {
  const payload = {
    title: chapter.value.title,
    content_json: chapter.value.content_json,
    content_html: chapter.value.content_html,
    status: chapter.value.status,
  }
  const { data } = await axios.post(`/books/api/chapters/${chapter.value.id}/update`, payload)
  if (data.success) {
    notify({ type: 'success', message: 'Salvato' })
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

        <v-list>
          <v-list-item
            v-for="c in chapters"
            :key="c.id"
            :title="c.title"
            :subtitle="c.status"
            @click="selectedChapterId = c.id; loadChapter(c.id)"
          />
        </v-list>
      </v-col>

      <v-col cols="12" md="8">
        <div v-if="!chapter" class="text-medium-emphasis">Seleziona un capitolo per modificarlo.</div>

        <div v-else>
          <div class="d-flex align-center mb-3">
            <h3 class="mr-3">Editor capitolo</h3>
            <v-spacer />
            <v-btn variant="tonal" color="green" @click="saveChapter">Salva</v-btn>
          </div>

          <v-text-field v-model="chapter.title" label="Titolo" />

          <RichTextEditor
            v-model="chapter.content_json"
            :course-id="props.courseId"
            @update:html="(html) => chapter.content_html = html"
          />

          <v-row class="mt-3">
            <v-col cols="12" md="4">
              <v-select
                v-model="chapter.status"
                :items="['draft','published']"
                label="Stato"
              />
            </v-col>
          </v-row>
        </div>
      </v-col>
    </v-row>
  </div>
</template>
