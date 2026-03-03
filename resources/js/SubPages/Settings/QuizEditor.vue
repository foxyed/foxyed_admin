<script setup>
import { inject, onMounted, ref } from 'vue'

const notify = inject('notify')

const props = defineProps({
  chapterId: { type: [Number, String], required: true },
})

const loading = ref(true)
const quiz = ref(null)
const questions = ref([])

const qModal = ref(false)
const editing = ref(null)
const qFields = ref({
  type: 'multiple_choice',
  prompt: '',
  points: 1,
  explanation: '',
  data: {
    options: [{ text: 'Opzione 1', correct: false }, { text: 'Opzione 2', correct: true }],
  },
})

const load = async () => {
  loading.value = true
  const { data } = await axios.get(`/quizzes/api/chapter/${props.chapterId}/quiz`)
  quiz.value = data.quiz
  questions.value = data.questions
  loading.value = false
}

onMounted(load)

const openCreate = () => {
  editing.value = null
  qFields.value = {
    type: 'multiple_choice',
    prompt: '',
    points: 1,
    explanation: '',
    data: { options: [{ text: 'Opzione 1', correct: false }, { text: 'Opzione 2', correct: true }] },
  }
  qModal.value = true
}

const openEdit = (row) => {
  editing.value = row
  qFields.value = {
    type: row.type,
    prompt: row.prompt,
    points: Number(row.points ?? 1),
    explanation: row.explanation ?? '',
    data: row.data ?? { options: [] },
  }
  qModal.value = true
}

const save = async () => {
  try {
    if (!editing.value) {
      const res = await axios.post(`/quizzes/api/quiz/${quiz.value.id}/questions/create`, qFields.value)
      if (res.data.success) {
        notify({ type: 'success', message: 'Domanda creata' })
        qModal.value = false
        await load()
      }
      return
    }

    const res = await axios.post(`/quizzes/api/questions/${editing.value.id}/update`, qFields.value)
    if (res.data.success) {
      notify({ type: 'success', message: 'Domanda aggiornata' })
      qModal.value = false
      await load()
    }
  } catch (e) {
    notify({ type: 'error', message: e?.response?.data?.message ?? 'Errore' })
  }
}

const removeQuestion = async (row) => {
  if (!confirm('Eliminare domanda?')) return
  const res = await axios.post(`/quizzes/api/questions/${row.id}/delete`)
  if (res.data.success) {
    notify({ type: 'success', message: 'Domanda eliminata' })
    await load()
  }
}

const updateQuiz = async () => {
  const res = await axios.post(`/quizzes/api/quiz/${quiz.value.id}/update`, {
    title: quiz.value.title,
    status: quiz.value.status,
    time_limit_seconds: quiz.value.time_limit_seconds,
    attempt_limit: quiz.value.attempt_limit,
  })
  if (res.data.success) {
    notify({ type: 'success', message: 'Quiz salvato' })
  }
}

const onTypeChange = () => {
  if (qFields.value.type === 'multiple_choice') {
    qFields.value.data = { options: qFields.value.data?.options?.length ? qFields.value.data : [{ text: 'Opzione 1', correct: true }] }
  }
  if (qFields.value.type === 'true_false') {
    qFields.value.data = { correct: true }
  }
  if (qFields.value.type === 'fill_blank') {
    qFields.value.data = { answers: [''] }
  }
}
</script>

<template>
  <div v-if="loading">Caricamento...</div>
  <div v-else>
    <div class="d-flex align-center mb-3">
      <h3 class="mr-3">Quiz fine capitolo</h3>
      <v-spacer />
      <v-btn variant="tonal" color="green" @click="updateQuiz">Salva quiz</v-btn>
    </div>

    <v-row>
      <v-col cols="12" md="6">
        <v-text-field v-model="quiz.title" label="Titolo" />
      </v-col>
      <v-col cols="12" md="3">
        <v-select v-model="quiz.status" :items="['draft','published']" label="Stato" />
      </v-col>
      <v-col cols="12" md="3">
        <v-text-field v-model.number="quiz.time_limit_seconds" label="Tempo (sec)" type="number" />
      </v-col>
    </v-row>

    <div class="d-flex align-center mb-3">
      <h4 class="mr-3">Domande</h4>
      <v-spacer />
      <v-btn @click="openCreate">
        <v-icon>mdi-plus</v-icon>
        Nuova domanda
      </v-btn>
    </div>

    <v-list>
      <v-list-item
        v-for="q in questions"
        :key="q.id"
        :title="`(${q.type}) ${q.prompt}`"
        :subtitle="`Punti: ${q.points}`"
      >
        <template #append>
          <v-btn variant="text" icon @click="openEdit(q)"><v-icon color="warning">mdi-pencil</v-icon></v-btn>
          <v-btn variant="text" icon @click="removeQuestion(q)"><v-icon color="error">mdi-delete</v-icon></v-btn>
        </template>
      </v-list-item>
    </v-list>

    <v-dialog v-model="qModal" max-width="800">
      <v-card>
        <v-card-actions>
          <v-card-title>{{ editing ? 'Modifica domanda' : 'Crea domanda' }}</v-card-title>
          <v-spacer />
          <v-btn @click="qModal=false" icon><v-icon>mdi-close</v-icon></v-btn>
        </v-card-actions>

        <v-container>
          <v-row>
            <v-col cols="12" md="4">
              <v-select
                v-model="qFields.type"
                :items="[
                  { title: 'Scelta multipla', value: 'multiple_choice' },
                  { title: 'Vero/Falso', value: 'true_false' },
                  { title: 'Riempi il vuoto', value: 'fill_blank' },
                ]"
                item-title="title"
                item-value="value"
                label="Tipo"
                @update:model-value="onTypeChange"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field v-model.number="qFields.points" label="Punti" type="number" />
            </v-col>
          </v-row>

          <v-textarea v-model="qFields.prompt" label="Domanda" />

          <template v-if="qFields.type === 'multiple_choice'">
            <div class="text-medium-emphasis mb-2">Opzioni (marca la/le corrette)</div>
            <v-row v-for="(opt, i) in qFields.data.options" :key="i">
              <v-col cols="9">
                <v-text-field v-model="opt.text" :label="`Opzione ${i+1}`" />
              </v-col>
              <v-col cols="3" class="d-flex align-center">
                <v-switch v-model="opt.correct" label="Corretta" />
              </v-col>
            </v-row>
            <v-btn variant="text" @click="qFields.data.options.push({text: '', correct: false})">+ aggiungi opzione</v-btn>
          </template>

          <template v-else-if="qFields.type === 'true_false'">
            <v-switch v-model="qFields.data.correct" label="Risposta corretta: Vero" />
          </template>

          <template v-else-if="qFields.type === 'fill_blank'">
            <div class="text-medium-emphasis mb-2">Risposte accettate</div>
            <v-text-field
              v-for="(a, i) in qFields.data.answers"
              :key="i"
              v-model="qFields.data.answers[i]"
              :label="`Risposta ${i+1}`"
            />
            <v-btn variant="text" @click="qFields.data.answers.push('')">+ aggiungi risposta</v-btn>
          </template>

          <v-textarea v-model="qFields.explanation" label="Spiegazione (feedback)" />
        </v-container>

        <v-card-actions>
          <v-spacer />
          <v-btn color="green" variant="tonal" @click="save">Conferma</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
