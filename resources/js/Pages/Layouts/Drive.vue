<script setup>

import Logged from "./Logged.vue";
import axios from "axios";
import {ref} from "vue";

const props = defineProps({
    currentPath: {
        type: String,
        default: "/"
    }
})

const file = ref(null)
const showFileUploader = ref(false)
const uploading = ref(false)

const createFolder = () => {

    const name = prompt("Nome cartella")

    if (!name) return

    axios.post('/drive/api/create-folder', {
        path: props.currentPath,
        name: name
    })
}

const onFileChange = (e) => {
    file.value = e.target.files[0]
}

const onFileUpload = async () => {

    if (!file.value) return

    uploading.value = true

    const formData = new FormData()
    formData.append("file", file.value)
    formData.append("path", props.currentPath)

    await axios.post("/drive/api/file-upload", formData, {
        headers: {
            "Content-Type": "multipart/form-data"
        }
    })

    uploading.value = false
    showFileUploader.value = false
    file.value = null
}

</script>

<template>
    <Logged>
        <template #sidebar>
            <v-list-item elevation="0">
                <v-menu>
                    <template #activator="{props}">
                        <v-btn v-bind="props" block prepend-icon="mdi-plus">Nuovo</v-btn>
                    </template>
                    <v-list density="compact">
                        <v-list-item
                            @click="createFolder"
                            density="compact"
                            title="Nuova cartella"
                            prepend-icon="mdi-folder-plus"
                        />
                        <v-list-item
                            @click="showFileUploader = true"
                            density="compact"
                            title="Carica file"
                            prepend-icon="mdi-upload"
                        />
                    </v-list>
                </v-menu>
            </v-list-item>
            <v-dialog v-model="showFileUploader" max-width="500">
                <v-card>
                    <v-card-title class="d-flex align-center">
                        Carica file
                        <v-spacer/>
                        <v-btn icon @click="showFileUploader = false">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-card-title>

                    <v-card-text>

                        <input
                            type="file"
                            @change="onFileChange"
                        />

                    </v-card-text>

                    <v-card-actions>
                        <v-spacer/>

                        <v-btn
                            :loading="uploading"
                            color="primary"
                            @click="onFileUpload"
                        >
                            Carica
                        </v-btn>

                    </v-card-actions>

                </v-card>
            </v-dialog>
        </template>

        <template
            v-for="(_, slotName) in $slots"
            #[slotName]="slotProps"
        >
            <slot :name="slotName" v-bind="slotProps"/>
        </template>

    </Logged>
</template>

<style scoped>

</style>
