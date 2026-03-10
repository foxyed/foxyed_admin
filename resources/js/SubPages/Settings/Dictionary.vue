<script setup>
import DataTable from "../../Components/DataTable.vue";
import {inject, ref, useTemplateRef} from "vue";

const notify = inject('notify');
const showModal = ref(false);
const showEdit = ref(false);
const fields = ref({
    createDictionary: {
        id: "",
        label: "",
        name: "",
        value: "",
        is_live: ""
    }
});

const dt = useTemplateRef('dt');

const onCreateDictionaryItem = async () => {
    const res = await axios.post('/settings/api/dictionary/create', {
        ...fields.value.createDictionary
    });
    if (res.data.success) {
        notify({
            type: 'success',
            message: "Record creato correttamente"
        });
        dt.value.reload();
        fields.value.createDictionary = {
            label: '',
            name: '',
            value: '',
            is_live: ''
        };
        showModal.value = false;

        return;
    }
    notify({
        type: 'error',
        message: res.data.message
    })
}

const onItemDelete = async (item) => {
    const ok = confirm("Vuoi eliminare questo record?");
    if (!ok) return;
    const res = await axios.post(`/settings/api/dictionary/${item.id}/delete`);
    if (!res.data.success) {
        notify({
            message: "Impossibile eliminare la variabile del registro",
            type: 'error'
        });
    } else {
        notify({
            message: "Variabile eliminata",
            type: 'success'
        });
    }
    dt.value.reload();
}

const showEditModal = async (item = null, action = 'show') => {
    if (action === 'show') {
        fields.value.createDictionary.id = item.id;
        fields.value.createDictionary.label = item.label;
        fields.value.createDictionary.name = item.key;
        fields.value.createDictionary.value = item.value;
        fields.value.createDictionary.is_live = item.is_live;
        showEdit.value = true;
        return;
    }
    if (action === 'hide') {
        fields.value.createDictionary = {
            label: "",
            name: "",
            value: "",
            is_live: ""
        }
        showEdit.value = false;
        return;
    }

    if (action === 'update') {
        const res = await axios.post(`/settings/api/dictionary/${fields.value.createDictionary.id}/edit`, {
            key: fields.value.createDictionary.name,
            value: fields.value.createDictionary.value,
            is_live: fields.value.createDictionary.is_live,
            label: fields.value.createDictionary.name
        });
        if (!res.data.success) {
            notify({
                message: "Impossibile aggiornare la variabile",
                type: "error"
            });
        } else {
            notify({
                message: "Variabile aggiornata",
                type: "success"
            });
        }
        dt.value.reload();
        await showEditModal(null, 'hide');
    }
}

</script>

<template>
    <DataTable ref="dt" :computed="[{key: 'actions', title: 'Azioni'}]"
               url="/settings/api/dictionary/list">
        <template #quick>
            <v-btn @click="showModal = true" icon="mdi-plus"></v-btn>
        </template>
        <template v-slot:item.is_live="{ item }">
            <div class="d-flex align-center justify-center">
                <v-switch
                    disabled
                    :model-value="item.is_live"
                    color="green"
                    hide-details
                    density="compact"
                />
            </div>
        </template>
        <template v-slot:item.actions="{item}">
            <div class="d-flex align-center justify-center">
                <v-btn @click="showEditModal(item)" v-tooltip="'Modifica'" icon variant="text">
                    <v-icon color="warning">mdi-pencil</v-icon>
                </v-btn>
                <v-btn @click="onItemDelete(item)" v-tooltip="'Elimina'" icon variant="text">
                    <v-icon color="error">mdi-delete</v-icon>
                </v-btn>
            </div>
        </template>
    </DataTable>

    <v-dialog v-model="showModal" max-width="500">
        <v-card>
            <v-card-actions>
                <v-card-title>Crea variabile</v-card-title>
                <v-spacer/>
                <v-btn @click="showModal = false" icon>
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-actions>
            <v-container>
                <v-text-field v-model="fields.createDictionary.label" label="Nome"/>
                <v-text-field v-model="fields.createDictionary.name" label="Codice parametro"/>
                <v-text-field v-model="fields.createDictionary.value" label="Valore"/>
                <v-switch value="on" v-model="fields.createDictionary.is_live" label="Attivo in produzione"/>
            </v-container>
            <v-card-actions>
                <v-spacer/>
                <v-btn @click="onCreateDictionaryItem" variant="tonal" color="green">Conferma</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <v-dialog v-model="showEdit" max-width="500">
        <v-card>
            <v-card-actions>
                <v-card-title>Modifica variabile</v-card-title>
                <v-spacer/>
                <v-btn @click="showEditModal(null,'hide')" icon>
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-actions>
            <v-container>
                <v-text-field v-model="fields.createDictionary.label" label="Nome"/>
                <v-text-field v-model="fields.createDictionary.name" label="Codice parametro"/>
                <v-text-field v-model="fields.createDictionary.value" label="Valore"/>
                <v-switch value="on" v-model="fields.createDictionary.is_live" label="Attivo in produzione"/>
            </v-container>
            <v-card-actions>
                <v-spacer/>
                <v-btn @click="showEditModal(null,'update')" variant="tonal" color="green">Conferma</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
