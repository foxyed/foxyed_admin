<script setup>
import {ref, watch} from 'vue'
import axios from 'axios'

const props = defineProps({
    url: {
        type: String,
        required: true
    },
    computed: {
        type: Array,
        required: false,
        default: []
    }
});

const headers = ref([])
const items = ref([])
const total = ref(0)
const loading = ref(false)

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
        }
    })

    headers.value = data.headers
    props.computed.forEach(el => {
        headers.value.push({
            key: el.key,
            title: el.title,
            align: el.align ?? "center",
            sortable: el.sortable ?? false
        });
    })
    items.value = data.items
    total.value = data.total

    loading.value = false
}

defineExpose({
    reload: loadItems
})

watch(options, loadItems, {deep: true, immediate: true})
</script>

<template>
    <v-data-table-server
        v-model:options="options"
        :headers="headers"
        :items="items"
        :items-length="total"
        :loading="loading"
    >
        <!-- 🔥 Forward di tutti gli slot -->
        <template
            v-for="(_, slotName) in $slots"
            #[slotName]="slotProps"
        >
            <slot :name="slotName" v-bind="slotProps"/>
        </template>
    </v-data-table-server>
</template>
