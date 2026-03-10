<script setup>
import {ref, watch} from 'vue'
import axios from 'axios'

defineOptions({
    inheritAttrs: false
})

const props = defineProps({
    url: {
        type: String,
        required: true
    },
    computed: {
        type: Array,
        default: () => []
    }
})

const headers = ref([])
const items = ref([])
const total = ref(0)
const loading = ref(false)
const showSearch = ref(false)
const search = ref('')

const options = ref({
    page: 1,
    itemsPerPage: 10,
    sortBy: []
})

const loadItems = async () => {
    loading.value = true

    const {data} = await axios.get(props.url, {
        params: {
            page: options.value.page,
            itemsPerPage: options.value.itemsPerPage,
            sortBy: options.value.sortBy?.[0]?.key,
            sortDesc: options.value.sortBy?.[0]?.order === 'desc',
            search: search.value
        }
    })

    headers.value = data.headers

    props.computed.forEach(el => {
        headers.value.push({
            key: el.key,
            title: el.title,
            align: el.align ?? "center",
            hidden: el.hidden ?? false,
            sortable: el.sortable ?? false
        })
    })

    items.value = data.items
    total.value = data.total

    loading.value = false
}

defineExpose({
    reload: loadItems
})

function debounce(fn, delay) {
    let timer
    return (...args) => {
        clearTimeout(timer)
        timer = setTimeout(() => fn(...args), delay)
    }
}

const updateSearch = debounce((value) => {
    search.value = value
}, 500)

watch(options, loadItems, {deep: true, immediate: true});
watch(search, loadItems, {deep: true, immediate: true})
</script>

<template>
    <v-data-table-server
        v-bind="$attrs"
        v-model:options="options"
        :headers="headers.filter(h => !h.hidden)"
        :items="items"
        :items-length="total"
        :loading="loading"
    >
        <template #top>
            <v-toolbar
            >
                <template v-slot:append>
                    <div class="d-flex ga-1 align-center">

                        <template v-if="showSearch">
                            <v-text-field
                                :model-value="search"
                                @update:modelValue="updateSearch"
                                density="compact"
                                hide-details
                                placeholder="Cerca..."
                                prepend-inner-icon="mdi-magnify"
                                @blur="showSearch = false"
                                style="min-width:180px; max-width:200px"
                            />
                        </template>

                        <template v-else>
                            <v-btn icon="mdi-magnify" @click="showSearch = true"></v-btn>
                        </template>

                        <slot name="quick"/>
                        <v-menu v-if="$slots.actions">
                            <template #activator="{props}">
                                <v-btn v-bind="props" icon="mdi-dots-vertical"></v-btn>
                            </template>

                            <v-list>
                                <slot name="actions"/>
                            </v-list>
                        </v-menu>
                    </div>
                </template>
            </v-toolbar>
        </template>
        <template
            v-for="(_, slotName) in $slots"
            #[slotName]="slotProps"
        >
            <slot :name="slotName" v-bind="slotProps"/>
        </template>
    </v-data-table-server>
</template>
