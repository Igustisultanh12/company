<script setup>
import { ChevronDown, ExternalLink, LogOut, Menu, ShieldCheck, UserRound } from '@lucide/vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import NotificationBell from '@/Components/panel/NotificationBell.vue';
import PanelSidebar from '@/Components/panel/PanelSidebar.vue';
import ThemeToggle from '@/Components/site/ThemeToggle.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import ConfirmHost from '@/Components/ui/ConfirmHost.vue';
import Dropdown from '@/Components/ui/Dropdown.vue';
import ToastHost from '@/Components/ui/ToastHost.vue';
import { useFlash } from '@/composables/useFlash';
import { dateLong } from '@/lib/format';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebar = ref(false);
const today = dateLong(new Date());

useFlash();

const logout = () => router.post(route('logout'));
</script>

<template>
    <div class="min-h-screen bg-surface">
        <PanelSidebar :open="sidebar" @close="sidebar = false" />

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-line bg-surface/80 backdrop-blur-xl">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" class="btn btn-secondary btn-icon lg:hidden" aria-label="Buka menu" @click="sidebar = true"><Menu class="h-5 w-5" /></button>
                    <p class="hidden text-sm font-semibold text-muted md:block">{{ today }}</p>
                    <div class="ml-auto flex items-center gap-1">
                        <ThemeToggle />
                        <NotificationBell />
                        <Dropdown width="w-60">
                            <template #trigger="{ open }">
                                <button type="button" class="flex items-center gap-2 rounded-2xl p-1 pr-2 transition hover:bg-surface-2" aria-label="Menu akun">
                                    <Avatar :user="user" size="sm" />
                                    <span class="hidden max-w-36 truncate text-sm font-bold sm:block">{{ user?.name }}</span>
                                    <ChevronDown :class="open ? 'rotate-180' : ''" class="hidden h-4 w-4 text-muted transition sm:block" />
                                </button>
                            </template>
                            <div class="border-b border-line px-4 py-3">
                                <p class="truncate text-sm font-bold">{{ user?.name }}</p>
                                <p class="truncate text-xs text-muted">{{ user?.email }}</p>
                            </div>
                            <div class="p-1.5">
                                <Link :href="route('panel.profile.edit')" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold hover:bg-surface-2"><UserRound class="h-4 w-4" /> Profil saya</Link>
                                <Link :href="route('panel.profile.edit', { tab: 'security' })" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold hover:bg-surface-2">
                                    <ShieldCheck class="h-4 w-4" /> Keamanan & 2FA
                                    <span v-if="!user?.two_factor_enabled" class="ml-auto h-2 w-2 rounded-full bg-amber-400"></span>
                                </Link>
                                <a :href="route('home')" target="_blank" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold hover:bg-surface-2"><ExternalLink class="h-4 w-4" /> Lihat website</a>
                                <button type="button" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10" @click="logout"><LogOut class="h-4 w-4" /> Keluar</button>
                            </div>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <main :key="page.component" class="page-enter px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <slot />
            </main>
        </div>
        <ToastHost />
        <ConfirmHost />
    </div>
</template>
