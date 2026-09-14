<template>
  <div class="chat-page">
    <div class="chat-sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-header">
        <h2 class="sidebar-title">{{ t('chat.title') }}</h2>
        <button class="btn-new-chat" @click="showNewChat = true">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
        </button>
      </div>

      <div v-if="loading" class="sidebar-loading">{{ t('product.loading') }}</div>

      <div v-else-if="conversations.length === 0" class="sidebar-empty">
        {{ t('chat.noConversations') }}
      </div>

      <div v-else class="conversation-list">
        <div
          v-for="conv in conversations"
          :key="conv.id"
          class="conv-item"
          :class="{ active: activeId === conv.id }"
          @click="selectConversation(conv)"
        >
          <img
            v-if="otherUser(conv).profile?.avatar"
            :src="otherUser(conv).profile.avatar"
            class="conv-avatar"
            alt=""
          />
          <span v-else class="conv-avatar conv-avatar-fallback">
            {{ (otherUser(conv).name || '?')[0] }}
          </span>
          <div class="conv-info">
            <div class="conv-top">
              <span class="conv-name">{{ otherUser(conv).name }}</span>
              <span v-if="conv.last_message_at" class="conv-time">
                {{ formatTime(conv.last_message_at) }}
              </span>
            </div>
            <div class="conv-bottom">
              <span v-if="conv.last_message" class="conv-preview">
                {{ conv.last_message.user_id === userId ? 'You: ' : '' }}
                <template v-if="conv.last_message.product_id">📦 Product</template>
                <template v-else>{{ conv.last_message.body || (conv.last_message.image ? '📷' : '') }}</template>
              </span>
              <span v-if="conv.unread_count > 0" class="conv-badge">
                {{ conv.unread_count > 99 ? '99+' : conv.unread_count }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="chat-main">
      <div v-if="!activeConversation" class="chat-placeholder">
        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="var(--border-strong)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <p>{{ t('chat.selectConversation') }}</p>
      </div>

      <template v-else>
        <div class="chat-header">
          <button class="back-btn" @click="goBack">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </button>
          <img
            v-if="otherUser(activeConversation).profile?.avatar"
            :src="otherUser(activeConversation).profile.avatar"
            class="chat-header-avatar"
            alt=""
          />
          <span v-else class="chat-header-avatar chat-header-fallback">
            {{ (otherUser(activeConversation).name || '?')[0] }}
          </span>
          <span class="chat-header-name">{{ otherUser(activeConversation).name }}</span>
        </div>

        <div ref="messagesContainer" class="messages-area">
          <div v-if="loadingMessages" class="messages-loading">{{ t('product.loading') }}</div>

          <div v-for="msg in messages" :key="msg.id" class="message-row" :class="{ mine: msg.user_id === userId }">
            <img
              v-if="msg.user_id !== userId && msg.user?.profile?.avatar"
              :src="msg.user.profile.avatar"
              class="msg-avatar"
              alt=""
            />
            <span v-else-if="msg.user_id !== userId" class="msg-avatar msg-avatar-fallback">
              {{ (msg.user?.name || '?')[0] }}
            </span>
            <div class="message-bubble">
              <div v-if="msg.product" class="msg-product-card" @click="viewProduct(msg.product.id)">
                <img
                  v-if="msg.product.image"
                  :src="`${STORAGE_URL}/storage/${msg.product.image}`"
                  class="msg-product-img"
                  alt=""
                />
                <div v-else class="msg-product-img msg-product-no-img">
                  <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
                <div class="msg-product-info">
                  <span class="msg-product-name">{{ msg.product.name }}</span>
                  <span class="msg-product-price">${{ Number(msg.product.price).toFixed(2) }}</span>
                </div>
              </div>
              <img v-if="msg.image" :src="`${STORAGE_URL}/storage/${msg.image}`" class="msg-image" alt="" />
              <p v-if="msg.body" class="msg-text">{{ msg.body }}</p>
              <span class="msg-time">{{ formatMsgTime(msg.created_at) }}</span>
            </div>
          </div>

          <div v-if="messages.length === 0 && !loadingMessages" class="messages-empty">
            {{ t('chat.noMessages') }}
          </div>
        </div>

        <div class="chat-input-area">
          <label class="img-upload-btn" :title="t('chat.uploadImage')">
            <input type="file" accept="image/*" class="sr-only" @change="onImageSelect" />
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
            </svg>
          </label>
          <div v-if="imagePreview" class="image-preview-wrap">
            <img :src="imagePreview" class="image-preview" alt="" />
            <button class="image-preview-remove" @click="removeImage">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <form class="msg-form" @submit.prevent="send">
            <input
              ref="msgInput"
              v-model="newMessage"
              type="text"
              class="msg-input"
              :placeholder="t('chat.typeMessage')"
              @keydown.enter.exact.prevent="send"
            />
            <button type="submit" class="send-btn" :disabled="!canSend">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
              </svg>
            </button>
          </form>
        </div>
      </template>
    </div>

    <!-- New Chat Modal -->
    <Teleport to="body">
      <div v-if="showNewChat" class="modal-overlay" @click.self="showNewChat = false">
        <div class="modal-card">
          <div class="modal-header">
            <h3>{{ t('chat.newChat') }}</h3>
            <button class="modal-close" @click="showNewChat = false">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <input
              v-model="userSearch"
              type="text"
              class="input"
              :placeholder="t('chat.searchUsers')"
              @input="onUserSearch"
            />
            <div class="user-search-results">
              <div v-if="searchingUsers" class="search-loading">{{ t('product.loading') }}</div>
              <div
                v-for="u in searchResults"
                :key="u.id"
                class="user-result"
                @click="startChat(u)"
              >
                <img v-if="u.profile?.avatar" :src="u.profile.avatar" class="user-result-avatar" alt="" />
                <span v-else class="user-result-avatar user-result-fallback">{{ (u.name || '?')[0] }}</span>
                <div>
                  <div class="user-result-name">{{ u.name }}</div>
                  <div class="user-result-email">{{ u.email }}</div>
                </div>
              </div>
              <div v-if="!searchingUsers && searchResults.length === 0 && userSearch.length > 1" class="search-empty">
                {{ t('chat.noUsers') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { t } from '../i18n'
import { getConversations, createConversation, getMessages, sendMessage, searchUsers } from '../services/chat'
import { STORAGE_URL } from '../services/http'
import { getEcho } from '../services/echo'

const router = useRouter()
const route = useRoute()

const userId = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}').id
  } catch {
    return null
  }
})

const conversations = ref([])
const activeConversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const imagePreview = ref(null)
const imageFile = ref(null)
const loading = ref(true)
const loadingMessages = ref(false)
const showNewChat = ref(false)
const userSearch = ref('')
const searchResults = ref([])
const searchingUsers = ref(false)
const sidebarOpen = ref(false)
const messagesContainer = ref(null)
const msgInput = ref(null)
let subscribedChannelId = null

const activeId = computed(() => activeConversation.value?.id)
const canSend = computed(() => newMessage.value.trim() || imagePreview.value)

function otherUser(conv) {
  if (!conv) return {}
  return conv.user1_id === userId.value ? conv.user2 : conv.user1
}

onMounted(async () => {
  try {
    const data = await getConversations()
    conversations.value = data.data || []

    const convId = Number(route.query.conversation)
    if (convId) {
      const conv = conversations.value.find(c => c.id === convId)
      if (conv) {
        activeConversation.value = conv
      }
    }
  } catch {
    // ignore
  } finally {
    loading.value = false
  }
})

watch(activeConversation, (conv) => {
  if (conv) {
    sidebarOpen.value = false
    loadMessages(conv.id)
    subscribeToChat(conv.id)
  }
})

async function loadMessages(conversationId) {
  loadingMessages.value = true
  try {
    const data = await getMessages(conversationId)
    messages.value = data.data?.data || []
    await nextTick()
    scrollToBottom()
  } catch {
    // ignore
  } finally {
    loadingMessages.value = false
  }
}

function subscribeToChat(conversationId) {
  const echo = getEcho()
  if (!echo) return

  if (subscribedChannelId && subscribedChannelId !== conversationId) {
    echo.leave(`chat.${subscribedChannelId}`)
  }

  subscribedChannelId = conversationId

  echo.private(`chat.${conversationId}`)
    .listen('.message.new', (event) => {
      if (event.user_id !== userId.value) {
        messages.value.push(event)
        nextTick(() => scrollToBottom())
      }

      // Update conversation in list
      const conv = conversations.value.find(c => c.id === conversationId)
      if (conv) {
        conv.last_message = event
        conv.last_message_at = event.created_at
        if (event.user_id !== userId.value && activeId.value !== conversationId) {
          conv.unread_count = (conv.unread_count || 0) + 1
        }
      }
    })
}

function selectConversation(conv) {
  activeConversation.value = conv
}

async function send() {
  if (!canSend.value || !activeConversation.value) return

  const body = newMessage.value.trim()
  const file = imageFile.value

  newMessage.value = ''
  removeImage()

  try {
    let data
    if (file) {
      const formData = new FormData()
      if (body) formData.append('body', body)
      formData.append('image', file)
      data = await sendMessage(activeConversation.value.id, formData)
    } else {
      const payload = {}
      if (body) payload.body = body
      data = await sendMessage(activeConversation.value.id, payload)
    }

    const msg = data.data

    messages.value.push(msg)
    await nextTick()
    scrollToBottom()

    // Update conversation
    const conv = conversations.value.find(c => c.id === activeConversation.value.id)
    if (conv) {
      conv.last_message = msg
      conv.last_message_at = msg.created_at
    }
  } catch {
    newMessage.value = body
  }
}

function onImageSelect(e) {
  const file = e.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) return

  imageFile.value = file

  const reader = new FileReader()
  reader.onload = (ev) => {
    imagePreview.value = ev.target.result
  }
  reader.readAsDataURL(file)

  e.target.value = ''
}

function removeImage() {
  imagePreview.value = null
  imageFile.value = null
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

let searchTimeout = null
function onUserSearch() {
  clearTimeout(searchTimeout)
  if (userSearch.value.length < 2) {
    searchResults.value = []
    return
  }
  searchingUsers.value = true
  searchTimeout = setTimeout(async () => {
    try {
      const data = await searchUsers(userSearch.value)
      searchResults.value = data.data || []
    } catch {
      searchResults.value = []
    } finally {
      searchingUsers.value = false
    }
  }, 300)
}

async function startChat(user) {
  try {
    const data = await createConversation(user.id)
    const conv = data.data

    // Add to list if new
    if (!conversations.value.find(c => c.id === conv.id)) {
      conversations.value.unshift(conv)
    }

    showNewChat.value = false
    userSearch.value = ''
    searchResults.value = []
    activeConversation.value = conv
  } catch {
    // ignore
  }
}

function goBack() {
  activeConversation.value = null
  sidebarOpen.value = true
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  const now = new Date()
  const date = new Date(dateStr)
  const diff = Math.floor((now - date) / 1000)
  if (diff < 60) return 'now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h`
  return `${Math.floor(diff / 86400)}d`
}

function formatMsgTime(dateStr) {
  const date = new Date(dateStr)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function viewProduct(productId) {
  router.push(`/products/${productId}`)
}

onUnmounted(() => {
  const echo = getEcho()
  if (echo && subscribedChannelId) {
    echo.leave(`chat.${subscribedChannelId}`)
  }
})
</script>

<style scoped>
.chat-page {
  display: flex;
  height: calc(100vh - 60px);
  overflow: hidden;
}

.chat-sidebar {
  width: 360px;
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  background: var(--surface);
  flex-shrink: 0;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
}

.sidebar-title {
  font-size: 1.25rem;
  font-weight: 700;
}

.btn-new-chat {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.45rem 0.75rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  transition: border-color 0.15s, color 0.15s;
}

.btn-new-chat:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.sidebar-loading,
.sidebar-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.conversation-list {
  flex: 1;
  overflow-y: auto;
}

.conv-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  cursor: pointer;
  border-bottom: 1px solid var(--border);
  transition: background 0.12s;
}

.conv-item:hover {
  background: var(--surface-2);
}

.conv-item.active {
  background: var(--accent-soft);
}

.conv-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.conv-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-weight: 600;
  font-size: 1rem;
}

.conv-info {
  flex: 1;
  min-width: 0;
}

.conv-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.conv-name {
  font-weight: 600;
  font-size: 0.9rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.conv-time {
  font-size: 0.7rem;
  color: var(--text-muted);
  flex-shrink: 0;
  margin-left: 0.5rem;
}

.conv-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.15rem;
}

.conv-preview {
  font-size: 0.8rem;
  color: var(--text-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

.conv-badge {
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 999px;
  background: var(--accent);
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  line-height: 18px;
  text-align: center;
  flex-shrink: 0;
  margin-left: 0.5rem;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.chat-placeholder {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-muted);
}

.chat-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--border);
  background: var(--surface);
}

.back-btn {
  display: none;
  align-items: center;
  justify-content: center;
  padding: 0.3rem;
  border: none;
  background: none;
  color: var(--text-muted);
  cursor: pointer;
}

.chat-header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.chat-header-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-weight: 600;
  font-size: 0.875rem;
}

.chat-header-name {
  font-weight: 600;
  font-size: 0.95rem;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.messages-loading,
.messages-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.message-row {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  max-width: 75%;
}

.message-row.mine {
  margin-left: auto;
  flex-direction: row-reverse;
}

.msg-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.msg-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--text-muted);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
}

.message-bubble {
  padding: 0.5rem 0.75rem;
  border-radius: var(--radius-md);
  background: var(--surface-2);
  border: 1px solid var(--border);
  max-width: 100%;
}

.message-row.mine .message-bubble {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.msg-image {
  max-width: 240px;
  max-height: 240px;
  border-radius: var(--radius-sm);
  display: block;
  margin-bottom: 0.25rem;
}

.msg-text {
  font-size: 0.9rem;
  line-height: 1.45;
  word-break: break-word;
  margin: 0;
}

.msg-time {
  font-size: 0.6rem;
  opacity: 0.6;
  display: block;
  margin-top: 0.2rem;
  text-align: right;
}

.chat-input-area {
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--border);
  background: var(--surface);
}

.img-upload-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;
  margin-bottom: 0.5rem;
  transition: border-color 0.15s, color 0.15s;
}

.img-upload-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0,0,0,0);
  border: 0;
}

.image-preview-wrap {
  position: relative;
  display: inline-block;
  margin-bottom: 0.5rem;
}

.image-preview {
  max-width: 120px;
  max-height: 120px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
}

.image-preview-remove {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: var(--danger);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
}

.msg-form {
  display: flex;
  gap: 0.5rem;
}

.msg-input {
  flex: 1;
  padding: 0.6rem 0.85rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--bg);
  color: var(--text);
  outline: none;
  font-size: 0.9rem;
}

.msg-input:focus {
  border-color: var(--accent);
}

.send-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  border: none;
  background: var(--accent);
  color: #fff;
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.15s;
}

.send-btn:hover:not(:disabled) {
  background: var(--accent-dark);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.msg-product-card {
  display: flex;
  gap: 0.6rem;
  padding: 0.5rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  cursor: pointer;
  margin-bottom: 0.35rem;
  transition: border-color 0.15s;
  max-width: 260px;
}

.msg-product-card:hover {
  border-color: var(--accent);
}

.message-row.mine .msg-product-card {
  background: rgba(255,255,255,0.15);
  border-color: rgba(255,255,255,0.25);
}

.message-row.mine .msg-product-card:hover {
  border-color: rgba(255,255,255,0.5);
}

.msg-product-img {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  flex-shrink: 0;
}

.msg-product-no-img {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--surface-2);
  color: var(--text-muted);
}

.msg-product-info {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 0;
}

.msg-product-name {
  font-size: 0.8rem;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.msg-product-price {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--accent);
}

.message-row.mine .msg-product-price {
  color: #fff;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
}

.modal-card {
  background: var(--surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  width: 100%;
  max-width: 420px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
}

.modal-header h3 {
  font-size: 1.1rem;
  font-weight: 700;
}

.modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.2rem;
}

.modal-body {
  padding: 1rem 1.25rem;
  overflow-y: auto;
}

.user-search-results {
  margin-top: 0.75rem;
}

.search-loading,
.search-empty {
  text-align: center;
  color: var(--text-muted);
  padding: 1rem 0;
  font-size: 0.85rem;
}

.user-result {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  transition: background 0.12s;
}

.user-result:last-child {
  border-bottom: none;
}

.user-result:hover {
  background: var(--surface-2);
}

.user-result-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.user-result-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-result-name {
  font-weight: 600;
  font-size: 0.9rem;
}

.user-result-email {
  font-size: 0.75rem;
  color: var(--text-muted);
}

@media (max-width: 768px) {
  .chat-page {
    flex-direction: column;
    height: calc(100vh - 60px - 52px);
  }

  .chat-sidebar {
    width: 100%;
    position: absolute;
    inset: 0;
    z-index: 10;
    transform: translateX(0);
    transition: transform 0.2s;
  }

  .chat-main {
    width: 100%;
  }

  .back-btn {
    display: flex;
  }

  .message-row {
    max-width: 85%;
  }
}
</style>
