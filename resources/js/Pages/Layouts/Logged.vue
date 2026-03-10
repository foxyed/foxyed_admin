<script setup>
import studentsImg from '@image/students.png';
import documentsImg from '@image/documents.png';
import coursesImg from '@image/courses.png';
import teachersImg from '@image/teachers.png';
import aiImg from '@image/ai.png';
import settingsImg from '@image/settings.png';
import {computed, provide, ref, watch} from "vue";
import {Link, usePage} from "@inertiajs/vue3";

const sidebarOpen = ref(false)
const isMobile = ref(window.innerWidth < 960)

const page = usePage()

const user = computed(() => page.props?.user)

function hasRole(...roles) {
    return roles.some(role => user.value?.groups?.includes(role))
}

const avatar = computed(() => {
    if (!user.value) return ""
    return `https://api.dicebear.com/9.x/adventurer/svg?seed=${
        encodeURIComponent(user.value.firstname + ' ' + user.value.lastname)
    }`
})

const notifications = ref([])

const showNotification = ({type, message}) => {
    notifications.value.push({
        color: type,
        text: message
    })
};

defineExpose({
    notify: showNotification,
});

provide('notify', showNotification);
provide('isMobile', isMobile);
watch(
    () => page.props.flash?.notification,
    (notification) => {
        if (notification) {
            notifications.value.push({
                text: notification.message,
                color: notification.type,
                timeout: 4000
            })
        }
    },
    {immediate: true}
)
</script>

<template>
    <v-app>
        <v-app-bar>
            <v-app-bar-nav-icon @click="sidebarOpen = !sidebarOpen"/>
            <v-spacer></v-spacer>
            <v-menu location="bottom">
                <template v-slot:activator="{props}">
                    <v-btn v-bind="props" icon>
                        <v-icon>mdi-apps</v-icon>
                    </v-btn>
                </template>
                <v-card min-height="200" min-width="300">
                    <v-container>
                        <v-row class="text-center">
                            <v-col v-if="hasRole('admin','administrative')" cols="4">
                               <v-card elevation="0" class="h-100 w-100 pa-2">
                                   <img style="height: 40px" :src="studentsImg" alt="students"/>
                                   <br/>
                                   <small>Studenti</small>
                               </v-card>
                            </v-col>
                            <v-col v-if="hasRole('admin','administrative', 'teacher')" cols="4">
                                <v-card elevation="0" class="h-100 w-100 pa-2">
                                    <img style="height: 40px" :src="documentsImg" alt="documents"/>
                                    <br/>
                                    <small>Documenti</small>
                                </v-card>
                            </v-col>
                            <v-col v-if="hasRole('admin','administrative', 'teacher')" cols="4">
                               <v-card elevation="0" class="h-100 w-100 pa-2">
                                   <img style="height: 40px" :src="coursesImg" alt="courses"/>
                                   <br/>
                                   <small>Corsi</small>
                               </v-card>
                            </v-col>
                            <v-col v-if="hasRole('admin','administrative')" cols="4">
                                <v-card elevation="0" class="h-100 w-100 pa-2">
                                    <img style="height: 40px" :src="teachersImg" alt="teachers"/>
                                    <br/>
                                    <small>Insegnanti</small>
                                </v-card>
                            </v-col>
                            <v-col cols="4">
                               <v-card elevation="0" class="h-100 w-100 pa-2">
                                   <img style="height: 40px" :src="aiImg" alt="teachers"/>
                                   <br/>
                                   <small>AI</small>
                               </v-card>
                            </v-col>
                            <v-col v-if="hasRole('admin')" cols="4">
                                <v-card class="h-100 w-100 pa-2"  :is="Link" href="/settings" elevation="0">
                                    <img style="height: 40px" :src="settingsImg" alt="Settings"/>
                                    <br/>
                                    <small>Impostazioni</small>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card>
            </v-menu>
            <v-menu location="bottom">
                <template v-slot:activator="{props}">
                    <v-btn v-bind="props" icon>
                        <v-avatar :image="avatar"/>
                    </v-btn>
                </template>
                <v-card min-width="250" min-height="150">
                    <v-list>
                        <v-list-item
                            :prepend-avatar="avatar"
                            :title="user.firstname+' '+user.lastname"
                            :subtitle="user.email"
                        />
                    </v-list>
                    <v-divider></v-divider>
                    <v-list>
                        <v-list-item
                            prepend-icon="mdi-account"
                            title="Profilo personale"
                        />
                        <v-list-item
                            :is="Link"
                            href="/auth/logout"
                            prepend-icon="mdi-logout"
                            title="Logout"
                        />
                    </v-list>
                </v-card>
            </v-menu>
        </v-app-bar>
        <v-navigation-drawer v-if="!isMobile" permanent rail>
            <v-list density="compact" nav>
                <v-list-item
                    v-if="hasRole('admin','administrative')"
                    v-tooltip="'Studenti'"
                    :prepend-avatar="studentsImg"/>
                <v-list-item
                    v-if="hasRole('admin','administrative', 'teacher')"
                    v-tooltip="'Documenti'"
                    :prepend-avatar="documentsImg"/>
                <v-list-item
                    v-if="hasRole('admin','administrative', 'teacher')"
                    v-tooltip="'Corsi'"
                    :prepend-avatar="coursesImg"/>
                <v-list-item
                    v-if="hasRole('admin','administrative')"
                    v-tooltip="'Insegnanti'"
                    :prepend-avatar="teachersImg"/>
                <v-list-item
                    v-tooltip="'Foxy AI'"
                    :prepend-avatar="aiImg"/>
                <v-list-item
                    :is="Link"
                    href="/settings"
                    v-if="hasRole('admin')"
                    v-tooltip="'Impostazioni'"
                    :prepend-avatar="settingsImg"/>
            </v-list>
        </v-navigation-drawer>
        <v-navigation-drawer v-model="sidebarOpen">
            <v-list>
                <v-list-item title="Dashboard" prepend-icon="mdi-home"/>
            </v-list>
        </v-navigation-drawer>
        <v-main>
            <v-container fluid>
                <slot/>
            </v-container>
        </v-main>
        <v-snackbar-queue v-model="notifications" location="top end">

        </v-snackbar-queue>
    </v-app>
</template>

<style scoped>

</style>
