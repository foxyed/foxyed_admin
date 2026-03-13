<script setup>
import Drive from "../Layouts/Drive.vue"
import {computed, onMounted, ref} from "vue"
import ContextMenu from '@imengyu/vue3-context-menu'

import axios from "axios";

const files = ref([])
const currentPath = ref("/")
const parentPath = ref(null);

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

    files.value = res.data.data;
    console.log(res.data.data);
    currentPath.value = res.data.current || "/"
    parentPath.value = res.data.parent
}

const onContextMenu = (e, type, path) => {
    e.preventDefault();
    if (type === 'folder') {
        ContextMenu.showContextMenu({
            theme: "mac dark",
            x: e.x,
            y: e.y,
            items: [
                {
                    label: "Apri",
                    icon: "mdi mdi-folder-open",
                    onClick: () => {
                        navigateToFolder(path)
                    }
                },
                {
                    label: "Rinomina",
                    icon: "mdi mdi-rename",
                    onClick: () => console.log("Rinomina"),
                },
                {
                    label: "Elimina",
                    icon: "mdi mdi-delete",
                    onClick: async () => {
                        const ok = window.confirm("Eliminare questa cartella?")
                        if (ok) {
                            await axios.post('/drive/api/delete-folder', {
                                name: path
                            });
                            await navigateToFolder(currentPath.value);
                        }

                    }
                },

            ]
        })
    } else {
        ContextMenu.showContextMenu({
            theme: "mac dark",
            x: e.x,
            y: e.y,
            items: [
                {
                    label: "Download",
                    icon: "mdi mdi-download",
                    onClick: () => console.log("Downloading...")
                },
                {
                    label: "Rinomina",
                    icon: "mdi mdi-cursor-text",
                    onClick: () => console.log("Rinomina")
                },
                {
                    label: "Elimina",
                    icon: "mdi mdi-delete",
                    onClick: async () => {
                        const ok = window.confirm("Eliminare questo file?")
                        if (ok) {
                            await axios.post('/drive/api/delete-folder', {
                                name: path
                            });
                            await navigateToFolder(currentPath.value);
                        }
                    }
                }
            ]
        });
    }
}

const pageContextMenu = (e) => {
    e.preventDefault();
    ContextMenu.showContextMenu({
        theme: 'mac dark',
        x: e.x,
        y: e.y,
        items: [
            {
                label: "Ricarica",
                icon: "mdi mdi-reload",
                onClick: () => {
                    navigateToFolder(currentPath.value)
                }
            },
            {
                label: "Informazioni",
                icon: "mdi mdi-information-variant-circle-outline",
                onClick: () => {

                }
            }
        ]
    })
}

const getIconFromType = (fileName) => {
    const split = fileName.split('.');
    const ext = split[split.length - 1];
    let icon = "mdi-";
    switch (ext) {
        case "png":
        case "jpeg":
        case "jpg":
            icon += "file-image-outline";
            break;
        case "pdf":
            icon += "file-pdf-box"
            break;
        case "book":
            icon += "book"
            break;
        default:
            icon += "file-document"

    }

    return icon;
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

    <Drive
        :current-path="currentPath"
        @contextmenu.prevent="pageContextMenu"
    >

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
                    :loading="file.loading"
                    v-if="file['.tag'] === 'folder'"
                    class="cursor-pointer"
                    variant="tonal"
                    subtitle="Cartella"
                    :title="file.name"
                    prepend-icon="mdi-folder"
                    @click.stop="file.loading = true; navigateToFolder(file.path_lower)"
                    @contextmenu.stop.prevent="e => onContextMenu(e,'folder', file.path_lower)"
                />

            </v-col>

        </v-row>
        <v-row v-if="files.length > 0">
            <v-col
                v-for="file in files"
                :key="file.id"
                cols="6"
                md="4"
                lg="3"
            >
                <v-card
                    :subtitle="file.name" v-if="file['.tag'] !== 'folder'"
                    :prepend-icon="getIconFromType(file.name)"
                    @contextmenu.stop.prevent="e => onContextMenu(e,'file', file.path_lower)"
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
