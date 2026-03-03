<script setup>
import { onBeforeUnmount, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'

const props = defineProps({
  modelValue: { type: Object, required: false, default: null },
  courseId: { type: [Number, String], required: false, default: null },
})

const emit = defineEmits(['update:modelValue', 'update:html'])

const editor = useEditor({
  content: props.modelValue ?? '',
  extensions: [
    StarterKit,
    Link.configure({ openOnClick: false }),
    Image.configure({ inline: false }),
  ],
  onUpdate({ editor }) {
    emit('update:modelValue', editor.getJSON())
    emit('update:html', editor.getHTML())
  },
})

watch(
  () => props.modelValue,
  (v) => {
    if (!editor.value) return
    // avoid loops
    const current = editor.value.getJSON()
    if (JSON.stringify(current) !== JSON.stringify(v)) {
      editor.value.commands.setContent(v ?? '', false)
    }
  }
)

const uploadImage = async () => {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/*'
  input.onchange = async () => {
    const file = input.files?.[0]
    if (!file) return

    const form = new FormData()
    form.append('file', file)
    if (props.courseId) form.append('course_id', props.courseId)

    const { data } = await axios.post('/media/api/upload', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    if (data.success) {
      editor.value?.chain().focus().setImage({ src: data.url }).run()
    }
  }
  input.click()
}

onBeforeUnmount(() => editor.value?.destroy())
</script>

<template>
  <div class="rte">
    <div class="rte-toolbar">
      <v-btn size="small" variant="text" @click="editor?.chain().focus().toggleBold().run()" :disabled="!editor">B</v-btn>
      <v-btn size="small" variant="text" @click="editor?.chain().focus().toggleItalic().run()" :disabled="!editor">I</v-btn>
      <v-btn size="small" variant="text" @click="editor?.chain().focus().toggleBulletList().run()" :disabled="!editor">
        <v-icon>mdi-format-list-bulleted</v-icon>
      </v-btn>
      <v-btn size="small" variant="text" @click="editor?.chain().focus().toggleOrderedList().run()" :disabled="!editor">
        <v-icon>mdi-format-list-numbered</v-icon>
      </v-btn>
      <v-btn size="small" variant="text" @click="uploadImage" :disabled="!editor">
        <v-icon>mdi-image</v-icon>
      </v-btn>
    </div>

    <EditorContent :editor="editor" class="rte-content" />
  </div>
</template>

<style scoped>
.rte {
  border: 1px solid rgba(0,0,0,0.12);
  border-radius: 8px;
}
.rte-toolbar {
  padding: 8px;
  border-bottom: 1px solid rgba(0,0,0,0.12);
}
.rte-content {
  padding: 12px;
  min-height: 280px;
}
</style>
