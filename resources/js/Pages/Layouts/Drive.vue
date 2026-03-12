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

const createFolder = () => {

    const name = prompt("Nome cartella")

    if (!name) return

    axios.post('/drive/api/create-folder', {
        path: props.currentPath,
        name: name
    })
}

const showFileUploader = ref(false);


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
            <v-dialog z-index="99999" v-model="showFileUploader" max-width="500">
                <v-card>
                    <v-card-actions>
                        <v-card-title>Carica file</v-card-title>
                        <v-spacer/>
                        <v-btn @click="showFileUploader = false">
                            <v-icon>mdi-close</v-icon>
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
