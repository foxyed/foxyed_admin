<script setup>

import { usePage } from "@inertiajs/vue3"
import Drive from "../Layouts/Drive.vue"
import { onMounted, ref, computed } from "vue"
import axios from "axios"

const page = usePage()

const files = ref([])
const currentPath = ref("/")
const parentPath = ref(null)

/*
|--------------------------------------------------------------------------
| Navigate
|--------------------------------------------------------------------------
*/

const navigateToFolder = async (path) => {

    const res = await axios.get("/drive/api", {
        params: {
            dir: path
        }
    })

    files.value = res.data.data
    currentPath.value = res.data.current || "/"
    parentPath.value = res.data.parent
}

/*
|--------------------------------------------------------------------------
| Breadcrumbs
|--------------------------------------------------------------------------
*/

const breadcrumbs = computed(() => {

    const parts = currentPath.value.split("/").filter(Boolean)

    let path = ""

    const items = [{
        title: "Home",
        path: "/"
    }]

    parts.forEach(part => {
        path += "/" + part

        items.push({
            title: part,
            path
        })
    })

    items[items.length - 1].disabled = true

    return items
})

onMounted(() => {
    navigateToFolder("/")
})

</script>


<template>

    <Drive>

        <!-- Toolbar -->

        <v-toolbar elevation="0" color="transparent">

            <v-btn
                v-if="currentPath !== '/'"
                icon="mdi-arrow-left"
                variant="text"
                @click="navigateToFolder(parentPath)"
            />

            <v-breadcrumbs divider="mdi-chevron-right">

                <v-breadcrumbs-item
                    v-for="(crumb,i) in breadcrumbs"
                    :key="i"
                >
                    <v-btn :disabled="crumb.disabled" @click="!crumb.disabled && navigateToFolder(crumb.path)">
                        {{ crumb.title }}
                    </v-btn>
                </v-breadcrumbs-item>

            </v-breadcrumbs>

        </v-toolbar>


        <!-- Files -->
        <v-row v-if="files.length > 0">

            <v-col
                v-for="file in files"
                :key="file.id"
                cols="6"
                md="4"
                lg="3"
            >

                <v-card
                    v-if="file['.tag'] === 'folder'"
                    class="cursor-pointer"
                    @click="navigateToFolder(file.path_lower)"
                    variant="tonal"
                    subtitle="Cartella"
                    :title="file.name"
                    prepend-icon="mdi-folder"
                />

            </v-col>

        </v-row>


        <!-- Empty state -->

        <v-empty-state
            v-else
            icon="mdi-folder-open"
            title="Nessun file qui"
        />

    </Drive>

</template>


<style scoped>

.cursor-pointer {
    cursor: pointer;
}

</style>
