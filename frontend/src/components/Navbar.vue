<template>
    <nav class="navbar">
        <div class="container nav-inner">
            <div class="brand-left">
                <RouterLink to="/" class="brand" @click="onHomeTabClick">
                    <svg
                        class="brand-mark"
                        viewBox="138.8 116 322.4 283"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-label="TRINITY"
                    >
                        <path
                            d="M 300 130 L 259.5 276.6 L 152.8 385 L 300 346.8 L 447.2 385 L 340.5 276.6 Z"
                            stroke="currentColor"
                            stroke-width="26"
                            stroke-linejoin="miter"
                            stroke-miterlimit="10"
                        />
                        <path
                            d="M 259.5 276.6 L 300 346.8 L 340.5 276.6 Z"
                            fill="currentColor"
                        />
                        <circle cx="300" cy="130" r="9" fill="currentColor" />
                        <circle cx="152.8" cy="385" r="9" fill="currentColor" />
                        <circle cx="447.2" cy="385" r="9" fill="currentColor" />
                    </svg>
                    <span class="brand-name">TRINITY</span>
                </RouterLink>
            </div>

            <div class="search-wrapper">
                <form class="search-box" @submit.prevent="onSearch">
                    <svg
                        class="search-icon"
                        viewBox="0 0 24 24"
                        width="16"
                        height="16"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        class="search-input input-bare"
                        :placeholder="t('nav.search')"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        class="search-clear"
                        @click="clearSearch"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            width="14"
                            height="14"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        >
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </form>
            </div>

            <div class="icon-actions">
                <!-- Search icon -->
                <RouterLink
                    v-if="!isProfilePage && !isSearchPage"
                    to="/search"
                    class="mobile-search-btn"
                    :title="t('nav.search')"
                    aria-label="Search"
                >
                    <svg
                        viewBox="0 0 24 24"
                        width="20"
                        height="20"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </RouterLink>

                <!-- Mobile favorites icon -->
                <RouterLink
                    v-if="isAuthenticated"
                    to="/favorites"
                    class="mobile-fav-btn"
                    :class="{ active: route.path === '/favorites' }"
                    aria-label="Favorites"
                >
                    <svg
                        viewBox="0 0 24 24"
                        width="20"
                        height="20"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                        />
                    </svg>
                </RouterLink>

                <!-- Mobile language switcher -->
                <LocaleSwitcher class="mobile-locale" />
                <button
                    class="theme-toggle"
                    type="button"
                    :aria-label="isDarkTheme ? 'Switch to light theme' : 'Switch to dark theme'"
                    :title="isDarkTheme ? 'Switch to light theme' : 'Switch to dark theme'"
                    @click="toggleTheme"
                >
                    <svg
                        v-if="isDarkTheme"
                        viewBox="0 0 24 24"
                        width="18"
                        height="18"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    >
                        <circle cx="12" cy="12" r="4" />
                        <line x1="12" y1="2" x2="12" y2="4" />
                        <line x1="12" y1="20" x2="12" y2="22" />
                        <line x1="4.93" y1="4.93" x2="6.34" y2="6.34" />
                        <line x1="17.66" y1="17.66" x2="19.07" y2="19.07" />
                        <line x1="2" y1="12" x2="4" y2="12" />
                        <line x1="20" y1="12" x2="22" y2="12" />
                        <line x1="4.93" y1="19.07" x2="6.34" y2="17.66" />
                        <line x1="17.66" y1="6.34" x2="19.07" y2="4.93" />
                    </svg>
                    <svg
                        v-else
                        viewBox="0 0 24 24"
                        width="18"
                        height="18"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                </button>
            </div>

            <div class="links">
                <template v-if="isAuthenticated">
                    <RouterLink
                        to="/products/create"
                        class="btn btn-primary btn-sm"
                    >
                        {{ t("nav.postProduct") }}
                    </RouterLink>

                    <!-- Notifications -->
                    <RouterLink to="/notifications" class="notif-trigger">
                        <svg
                            viewBox="0 0 24 24"
                            width="18"
                            height="18"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"
                            />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span v-if="unreadCount > 0" class="notif-badge">{{
                            unreadCount > 99 ? "99+" : unreadCount
                        }}</span>
                    </RouterLink>

                    <!-- Favorites -->
                    <RouterLink to="/favorites" class="notif-trigger">
                        <svg
                            viewBox="0 0 24 24"
                            width="18"
                            height="18"
                            fill="var(--accent)"
                            stroke="var(--accent)"
                            stroke-width="2"
                        >
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                            />
                        </svg>
                    </RouterLink>

                    <!-- Chat -->
                    <RouterLink
                        to="/chat"
                        class="notif-trigger"
                        @click="onChatClick"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            width="18"
                            height="18"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                            />
                        </svg>
                        <span
                            v-if="chatUnread > 0"
                            class="notif-badge chat-badge"
                            >{{ chatUnread > 99 ? "99+" : chatUnread }}</span
                        >
                    </RouterLink>

                    <RouterLink
                        to="/profile"
                        class="btn btn-ghost user-trigger"
                    >
                        <span class="user-name">{{ userName }}</span>
                        <img
                            v-if="userAvatar"
                            :src="userAvatar"
                            class="user-avatar"
                            alt=""
                        />
                        <span v-else class="user-avatar user-avatar-fallback">{{
                            userInitial
                        }}</span>
                    </RouterLink>
                </template>
                <template v-else>
                    <RouterLink to="/login" class="btn btn-ghost">{{
                        t("nav.login")
                    }}</RouterLink>
                    <RouterLink to="/register" class="btn btn-primary">{{
                        t("nav.register")
                    }}</RouterLink>
                </template>
                <LocaleSwitcher />
            </div>
        </div>
    </nav>

    <!-- Bottom tab bar (mobile/tablet) -->
    <nav class="bottom-bar">
        <RouterLink
            to="/"
            class="bottom-tab"
            :class="{ active: route.path === '/' }"
            @click="onHomeTabClick"
        >
            <svg
                viewBox="0 0 24 24"
                width="22"
                height="22"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            <span>{{ t("nav.home") }}</span>
        </RouterLink>
        <RouterLink
            to="/products/create"
            class="bottom-tab"
            :class="{ active: route.path === '/products/create' }"
        >
            <span class="bottom-tab-plus">+</span>
            <span>{{ t("nav.postProduct") }}</span>
        </RouterLink>
        <RouterLink
            to="/notifications"
            class="bottom-tab"
            :class="{ active: route.path === '/notifications' }"
        >
            <span class="bottom-tab-icon-wrap">
                <svg
                    viewBox="0 0 24 24"
                    width="22"
                    height="22"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
                <span v-if="unreadCount > 0" class="bottom-notif-badge">{{
                    unreadCount > 99 ? "99+" : unreadCount
                }}</span>
            </span>
            <span>{{ t("nav.notifications") }}</span>
        </RouterLink>
        <RouterLink
            to="/chat"
            class="bottom-tab"
            :class="{ active: route.path === '/chat' }"
            @click="onChatClick"
        >
            <span class="bottom-tab-icon-wrap">
                <svg
                    viewBox="0 0 24 24"
                    width="22"
                    height="22"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                    />
                </svg>
                <span v-if="chatUnread > 0" class="bottom-notif-badge">{{
                    chatUnread > 99 ? "99+" : chatUnread
                }}</span>
            </span>
            <span>{{ t("nav.chat") }}</span>
        </RouterLink>
        <RouterLink
            to="/profile"
            class="bottom-tab"
            :class="{ active: route.path === '/profile' }"
        >
            <img
                v-if="userAvatar"
                :src="userAvatar"
                class="bottom-tab-avatar"
                alt=""
            />
            <span v-else class="bottom-tab-avatar bottom-tab-avatar-fallback">{{
                userInitial
            }}</span>
            <span>{{ t("nav.myProfile") }}</span>
        </RouterLink>
    </nav>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { t } from "../i18n";
import { getUser } from "../services/auth";
import { resolveStorageUrl } from "../services/http";
import { getUnreadCount } from "../services/notifications";
import { getConversations } from "../services/chat";
import { initEcho, leaveEcho } from "../services/echo";
import LocaleSwitcher from "./LocaleSwitcher.vue";
import { chatUnread, setChatUnread, addChatUnread, resetChatUnread } from "../stores/chat";
import { pushToast } from "../stores/toast";

const router = useRouter();
const route = useRoute();
const isProfilePage = computed(() => route.name === "profile");
const isSearchPage = computed(() => route.name === "search");
const isAuthenticated = ref(!!localStorage.getItem("token"));
const searchQuery = ref("");
const searchInputRef = ref(null);
const isDarkTheme = ref(document.documentElement.dataset.theme === "dark");

const unreadCount = ref(0);
const conversations = ref([]);

const user = ref(readStoredUser());
const userName = computed(() => user.value.name || "");
const userAvatar = computed(() => resolveStorageUrl(user.value.profile?.avatar || ""));
const userInitial = computed(() =>
    (userName.value || "?").trim().charAt(0).toUpperCase(),
);

function readStoredUser() {
    try {
        return JSON.parse(localStorage.getItem("user") || "{}");
    } catch {
        return {};
    }
}

onMounted(() => {
    refreshAuth();
    window.addEventListener("auth-changed", refreshAuth);
    window.addEventListener("storage", handleThemeStorage);
});

onUnmounted(() => {
    leaveEcho();
    window.removeEventListener("notifications-read", loadUnreadCount);
    window.removeEventListener("auth-changed", refreshAuth);
    window.removeEventListener("storage", handleThemeStorage);
});

async function refreshAuth() {
    isAuthenticated.value = !!localStorage.getItem("token");

    if (!isAuthenticated.value) {
        user.value = {};
        return;
    }

    try {
        const data = await getUser();
        user.value = data.data || {};
        localStorage.setItem("user", JSON.stringify(user.value));
        loadUnreadCount();
        loadChatUnread();
        try {
            connectEcho();
        } catch {}
        window.addEventListener("notifications-read", loadUnreadCount);
    } catch {
        logout();
    }
}

function connectEcho() {
    const echo = initEcho(user.value.id);
    if (!echo) return;

    const userChannel = echo.private(`App.Models.User.${user.value.id}`);

    userChannel.listen(".notification.created", (payload) => {
        unreadCount.value++;
        notifyLive(payload);
    });

    userChannel.listen(".chat.unread", (event) => {
        if (route.name === "chat") {
            window.dispatchEvent(
                new CustomEvent("chat-unread", { detail: event }),
            );
            return;
        }

        addChatUnread();
        notifyChat(event);
    });
}

function notifyLive(payload) {
    const data = payload?.data || payload || {};
    const who = data.user?.name;

    const message = data.type === "comment_created" && who
        ? t("toast.commentedOn", { name: who, product: data.product_name || "" })
        : t("toast.newActivity");

    pushToast({
        type: "info",
        title: t("toast.title"),
        message,
        action: t("toast.view"),
        onAction: () => router.push("/notifications"),
    });
}

function notifyChat(event) {
    const conversationId = event?.conversation_id;
    const message = event?.message;
    if (!conversationId || !message) return;

    const conversation = conversations.value.find(
        (conv) => conv.id === conversationId,
    );
    const who =
        conversation?.user1?.name || conversation?.user2?.name || "";

    let preview = message.body || "";
    if (!preview && message.image) preview = t("toast.sentImage");
    if (!preview && message.voice) preview = t("toast.sentVoice");

    pushToast({
        type: "info",
        title: t("toast.newMessage"),
        message: who ? `${who}: ${preview}`.trim() : preview,
        action: t("toast.openChat"),
        onAction: () => router.push(`/chat/${conversationId}`),
    });
}

async function loadUnreadCount() {
    try {
        const count = await getUnreadCount();
        unreadCount.value = count.data?.count || 0;
    } catch {}
}

async function loadChatUnread() {
    if (route.name === "chat") return;
    try {
        const data = await getConversations();
        const list = data.data || [];
        conversations.value = list;
        let total = 0;
        for (const conv of list) total += conv.unread_count || 0;
        setChatUnread(total);
    } catch {}
}

function logout() {
    const current = JSON.parse(localStorage.getItem("user") || "{}");
    const token = localStorage.getItem("token");
    if (current.id && token) {
        const saved = JSON.parse(
            localStorage.getItem("saved_accounts") || "[]",
        );
        if (!saved.find((a) => a.id === current.id)) {
            saved.push({ ...current, _token: token });
            localStorage.setItem("saved_accounts", JSON.stringify(saved));
        }
    }
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    isAuthenticated.value = false;
    user.value = {};
    resetChatUnread();
    router.push("/login");
}

function onSearch() {
    const q = searchQuery.value.trim();
    if (!q) return;
    router.push({ path: "/products", query: { q } });
    searchQuery.value = "";
}

function clearSearch() {
    searchQuery.value = "";
    searchInputRef.value?.focus();
}

function applyTheme(theme) {
    isDarkTheme.value = theme === "dark";
    document.documentElement.dataset.theme = isDarkTheme.value
        ? "dark"
        : "light";
}

function toggleTheme() {
    const theme = isDarkTheme.value ? "light" : "dark";
    applyTheme(theme);
    try {
        localStorage.setItem("trinity-theme", theme);
    } catch {}
    window.dispatchEvent(
        new CustomEvent("theme-changed", { detail: { theme } }),
    );
}

function handleThemeStorage(e) {
    if (e.key !== "trinity-theme") return;
    if (e.newValue === "light" || e.newValue === "dark") {
        applyTheme(e.newValue);
    }
}

function onHomeTabClick() {
    if (route.path === "/") {
        window.dispatchEvent(new CustomEvent("home-refresh"));
    }
}

function onChatClick() {
    if (route.path === "/chat") {
        window.dispatchEvent(new CustomEvent("chat-show-list"));
    }
}
</script>

<style scoped>
.navbar {
    --nav-btn-size: 38px;
    position: sticky;
    top: 0;
    z-index: 50;
    background: var(--navbar-bg);
    border-bottom: 1px solid var(--border);
    box-shadow: 0 1px 0 var(--border), var(--shadow-sm);
}

.nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 70px;
    padding-top: 0.7rem;
    padding-bottom: 0.7rem;
}

.nav-inner .links > *,
.icon-actions > * {
    flex: 0 0 auto;
}

.brand {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    text-decoration: none;
    color: var(--text);
    font-family: var(--font-serif);
    font-weight: 600;
    font-size: 1.3rem;
    letter-spacing: 0.14em;
}

.brand-mark {
    width: 26px;
    height: 26px;
    color: var(--accent);
}

.brand-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.links {
    display: flex;
    gap: 0.6rem;
    align-items: center;
}

.icon-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-right: 0.6rem;
}

.theme-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: var(--nav-btn-size);
    height: var(--nav-btn-size);
    padding: 0;
    flex: 0 0 var(--nav-btn-size);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--text-muted);
}

.theme-toggle:hover {
    color: var(--primary);
    border-color: var(--accent);
    background: var(--accent-soft);
}

:deep(.mobile-locale) {
    display: none;
}

.search-wrapper {
    display: flex;
    position: relative;
    flex: none;
    width: 100%;
    max-width: 320px;
    margin-left: auto;
    margin-right: 0.6rem;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    width: 100%;
    height: var(--nav-btn-size);
    padding: 0 0.75rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: var(--surface);
}

.search-box:focus-within {
    border-color: var(--accent);
}

.search-icon {
    color: var(--text-muted);
    flex-shrink: 0;
}

.search-input {
    font-size: 0.85rem;
}

.search-input::placeholder {
    color: var(--text-muted);
}

.search-clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
}

.search-clear:hover {
    color: var(--text);
}

.links .btn {
    min-height: var(--nav-btn-size);
    height: var(--nav-btn-size);
    padding: 0 0.85rem;
    font-size: 0.85rem;
}

.link {
    text-decoration: none;
    color: var(--text-muted);
    font-weight: 500;
    font-size: 0.95rem;
    padding: 0.5rem 0.85rem;
    border-radius: var(--radius-sm);
}

.link:hover {
    color: var(--text);
    background: var(--surface-2);
}

.link.router-link-active {
    color: var(--accent);
    background: var(--accent-soft);
}

.user-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    text-decoration: none;
    color: var(--text);
    padding: 0.2rem 0.2rem 0.2rem 0.6rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: var(--navbar-bg);
}

.user-chip:hover {
    border-color: var(--accent);
}

.user-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
}

.user-avatar-fallback {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--accent);
    color: #fff;
    font-weight: 600;
    font-size: 0.9rem;
}

.user-name {
    font-size: 0.85rem;
    font-weight: 600;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    height: var(--nav-btn-size);
    padding: 0 0.85rem;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    cursor: pointer;
}


.user-trigger:hover {
    border-color: var(--border-strong);
}

.notif-trigger {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: var(--nav-btn-size);
    height: var(--nav-btn-size);
    padding: 0;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--text-muted);
    cursor: pointer;
    text-decoration: none;
}


.notif-trigger:hover {
    border-color: var(--border-strong);
    color: var(--text);
}

.chat-badge {
    top: -5px;
    right: -3px;
}

.notif-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    border-radius: 999px;
    background: var(--danger);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    line-height: 16px;
    text-align: center;
}

.mobile-search-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: var(--nav-btn-size);
    height: var(--nav-btn-size);
    padding: 0;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--text-muted);
    text-decoration: none;
    flex: 0 0 var(--nav-btn-size);
}

.mobile-search-btn:hover {
    border-color: var(--border-strong);
    color: var(--accent);
}

.mobile-fav-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: var(--nav-btn-size);
    height: var(--nav-btn-size);
    padding: 0;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--text-muted);
    text-decoration: none;
    flex: 0 0 var(--nav-btn-size);
}

.mobile-fav-btn:hover,
.mobile-fav-btn.active {
    border-color: var(--border-strong);
    color: var(--accent);
}

.links :deep(.locale-trigger),
.icon-actions :deep(.locale-trigger) {
    height: var(--nav-btn-size);
    padding-top: 0;
    padding-bottom: 0;
}

@media (max-width: 992px) {
    .user-name {
        display: none;
    }
}

@media (max-width: 992px) {
    .search-wrapper {
        display: none;
    }

    .icon-actions {
        margin-left: auto;
    }

    .mobile-search-btn {
        display: inline-flex;
    }

    .links {
        display: none;
    }

    .mobile-fav-btn {
        display: inline-flex;
    }

    :deep(.mobile-locale) {
        display: inline-flex;
    }
}

@media (max-width: 480px) {
    .brand {
        font-size: 0.95rem;
        letter-spacing: 0.06em;
    }

    .brand-mark {
        width: 20px;
        height: 20px;
    }

    .icon-actions {
        gap: 0.25rem;
        margin-right: 0;
    }

    .icon-actions :deep(.locale-trigger) {
        width: var(--nav-btn-size);
        padding: 0;
        justify-content: center;
    }

    .icon-actions :deep(.locale-code) {
        display: none;
    }
}

/* Bottom tab bar */
.bottom-bar {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 50;
    background: var(--navbar-bg);
    border-top: 1px solid var(--border);
    padding: 0.35rem 0 max(0.35rem, env(safe-area-inset-bottom));
}

.bottom-tab {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    flex: 1;
    padding: 0.35rem 0;
    text-decoration: none;
    color: var(--text-muted);
    font-size: 0.65rem;
    font-weight: 500;
    -webkit-tap-highlight-color: transparent;
}

.bottom-tab.active {
    color: var(--accent);
}

.bottom-tab-plus {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--accent);
    color: #fff;
    font-size: 1.2rem;
    font-weight: 300;
    line-height: 1;
}

.bottom-tab-icon-wrap {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 26px;
}

.bottom-notif-badge {
    position: absolute;
    top: -4px;
    right: -8px;
    min-width: 15px;
    height: 15px;
    padding: 0 4px;
    border-radius: 999px;
    background: var(--danger);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    line-height: 15px;
    text-align: center;
}

.bottom-tab-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
}

.bottom-tab-avatar-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--accent);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 600;
}

@media (max-width: 992px) {
    .bottom-bar {
        display: flex;
    }
}
</style>
