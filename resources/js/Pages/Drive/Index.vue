<script setup>

import {usePage} from "@inertiajs/vue3";
import Drive from "../Layouts/Drive.vue";

const page = usePage();

const folders = page.props.folders;


const navigateTofolder = (path) => {

}
</script>

<template>
    <Drive>
        <v-row>
            <v-col sm="12" lg="4">
                <v-card @click="navigateTofolder(folder.path_lower)" variant="tonal" subtitle="Cartella"
                        :title="folder.name" v-for="folder in folders" :key="folder" prepend-icon="mdi-folder"></v-card>
            </v-col>
        </v-row>
    </Drive>
</template>

<style scoped>

</style>
