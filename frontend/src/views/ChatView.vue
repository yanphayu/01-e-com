<template>
  <div class="chat-page">
    <div v-if="sidebarOpen && !activeConversation" class="sidebar-scrim" @click="sidebarOpen = false"></div>
    <div class="chat-sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-header">
        <h2 class="sidebar-title">{{ t('chat.title') }}</h2>
        <button
          class="btn-archived"
          :class="{ active: isArchivedView }"
          :title="t('chat.archived')"
          @click="toggleArchivedView"
        >
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>
          </svg>
        </button>
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

      <div v-else-if="visibleConversations.length === 0" class="sidebar-empty">
        {{ t('chat.noConversations') }}
      </div>

      <div v-else class="conversation-list">
        <div
          v-for="conv in visibleConversations"
          :key="conv.id"
          class="conv-item"
          :class="{ active: activeId === conv.id }"
          @click="selectConversation(conv)"
        >
          <img
            v-if="otherUser(conv).profile?.avatar"
            :src="resolveStorageUrl(otherUser(conv).profile.avatar)"
            class="conv-avatar"
            alt=""
          />
          <span v-else class="conv-avatar conv-avatar-fallback">
            {{ (otherUser(conv).name || '?')[0] }}
          </span>
          <div class="conv-info">
            <div class="conv-top">
              <span class="conv-name">
                <svg
                  v-if="conv.is_pinned"
                  class="conv-pinned"
                  viewBox="0 0 24 24"
                  width="12"
                  height="12"
                  fill="currentColor"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linejoin="round"
                >
                  <path d="M12 17v5"/><path d="M5 3h14v5a4 4 0 0 1-4 4h-6a4 4 0 0 1-4-4z"/>
                </svg>
                {{ otherUser(conv).name }}
              </span>
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
              <span class="conv-right">
                <svg
                  v-if="conv.is_muted"
                  class="conv-muted"
                  viewBox="0 0 24 24"
                  width="13"
                  height="13"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                  <path d="M23 9l-6 6"/><path d="M17 9l6 6"/>
                </svg>
                <span v-if="conv.unread_count > 0" class="conv-badge">
                  {{ conv.unread_count > 99 ? '99+' : conv.unread_count }}
                </span>
              </span>
            </div>
          </div>
          <button class="conv-more" :title="t('chat.more')" @click.stop="openMenu(conv, $event)">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
            </svg>
          </button>
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
          <button class="chat-header-user" @click="showProfileInfo = true">
            <img
              v-if="otherUser(activeConversation).profile?.avatar"
              :src="resolveStorageUrl(otherUser(activeConversation).profile.avatar)"
              class="chat-header-avatar"
              alt=""
            />
            <span v-else class="chat-header-avatar chat-header-fallback">
              {{ (otherUser(activeConversation).name || '?')[0] }}
            </span>
            <span class="chat-header-name">{{ otherUser(activeConversation).name }}</span>
          </button>
          <button class="chat-header-details" @click="showProfileInfo = true" :title="t('chat.details')">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/>
            </svg>
          </button>
        </div>

        <div ref="messagesContainer" class="messages-area">
          <div v-if="loadingMessages" class="messages-loading">{{ t('product.loading') }}</div>

          <div
            v-for="msg in messages"
            :key="msg.id"
            :id="`message-${msg.id}`"
            class="message-row"
            :class="{ mine: msg.user_id === userId }"
            @contextmenu.prevent="onMessageMenu(msg, $event)"
          >
            <img
              v-if="msg.user_id !== userId && msg.user?.profile?.avatar"
              :src="resolveStorageUrl(msg.user.profile.avatar)"
              class="msg-avatar"
              alt=""
            />
            <span v-else-if="msg.user_id !== userId" class="msg-avatar msg-avatar-fallback">
              {{ (msg.user?.name || '?')[0] }}
            </span>
            <div class="message-bubble" :class="{ deleted: msg.deleted_at }">
              <span v-if="msg.is_pinned" class="msg-pin-badge">
                <svg viewBox="0 0 24 24" width="11" height="11" fill="currentColor">
                  <path d="M16 3l5 5-2.5 2.5L20 12v3l-4 2v4h-2l-3-3-5 4-3-3 4-5-3-3v-2h3l1.5-1.5L16 3z"/>
                </svg>
              </span>

              <button
                v-if="msg.replied_message"
                type="button"
                class="msg-reply-quote"
                @click.stop="jumpToMessage(msg.replied_message.id)"
              >
                <span class="msg-reply-owner">
                  {{ msg.replied_message.user_id === userId ? t('chat.you') : (msg.replied_message.user?.name || '') }}
                </span>
                <span class="msg-reply-text">{{ replyPreview(msg.replied_message) }}</span>
              </button>

              <div v-if="msg.deleted_at" class="msg-deleted-text">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                {{ msg.user_id === userId ? t('chat.msgDeletedYou') : t('chat.msgDeleted') }}
              </div>

              <template v-else>
                <div v-if="msg.product" class="msg-product-card" @click.stop="viewProduct(msg.product.id)">
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
                <span v-if="msg.forwarded" class="msg-forwarded-label">{{ t('chat.forwarded') }}</span>
                <p v-if="msg.body" class="msg-text">{{ msg.body }}</p>
              </template>

              <span class="msg-footer">
                <span class="msg-time">
                  {{ formatMsgTime(msg.created_at) }}{{ msg.edited_at ? ` · ${t('chat.edited')}` : '' }}
                </span>
                <svg
                  v-if="msg.user_id === userId"
                  class="msg-status"
                  :class="msg.read_at ? 'seen' : 'sent'"
                  viewBox="0 0 24 24"
                  width="15"
                  height="15"
                  aria-hidden="true"
                >
                  <path
                    v-if="msg.read_at"
                    d="M1 12l4.5 4.5L17 5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M5 12l4.5 4.5L21 5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>

            </div>

            <button
              v-if="!msg.deleted_at"
              type="button"
              class="message-more-btn"
              :title="t('chat.more')"
              :aria-label="t('chat.more')"
              @click.stop="openMessageMenu(msg, $event)"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
                <circle cx="5" cy="12" r="1.5" />
                <circle cx="12" cy="12" r="1.5" />
                <circle cx="19" cy="12" r="1.5" />
              </svg>
            </button>

          </div>

          <div v-if="messages.length === 0 && !loadingMessages" class="messages-empty">
            {{ t('chat.noMessages') }}
          </div>
        </div>

        <div v-if="draftProduct" class="product-draft-wrap">
          <div class="msg-product-card msg-product-draft">
            <img
              v-if="draftImage"
              :src="draftImage"
              class="msg-product-img"
              alt=""
            />
            <div v-else class="msg-product-img msg-product-no-img">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
              </svg>
            </div>
            <div class="msg-product-info">
              <span class="msg-product-name">
                <span class="product-draft-label">{{ t('chat.productDraft') }}</span>
                {{ draftProduct.name }}
              </span>
              <span class="msg-product-price">${{ Number(draftProduct.price).toFixed(2) }}</span>
            </div>
            <button class="product-draft-remove" @click="clearDraftProduct" :title="t('chat.removeDraft')">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="chat-input-area">
          <div v-if="replyTo || editingMessage" class="composer-preview">
            <div class="composer-preview-content">
              <span class="composer-preview-label">
                {{ editingMessage ? t('chat.editing') : t('chat.replyTo') }}
                <template v-if="replyTo">
                  {{ replyTo.user_id === userId ? t('chat.you') : (replyTo.user?.name || '') }}
                </template>
              </span>
              <span class="composer-preview-text">{{ composerPreviewText() }}</span>
            </div>
            <button class="composer-preview-close" @click="cancelComposer">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
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
            <button type="submit" class="send-btn">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
              </svg>
            </button>
          </form>
        </div>
      </template>
    </div>

    <!-- Profile info (Messenger-style Details) -->
    <div v-if="showProfileInfo && activeConversation" class="pf-scrim" @click="showProfileInfo = false"></div>

      <aside v-if="showProfileInfo && activeConversation" class="profile-info">
        <div class="pf-header">
          <h3 class="pf-title">{{ t('chat.details') }}</h3>
          <button class="pf-close" @click="showProfileInfo = false" :title="t('chat.close')">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div class="pf-scroll">
          <div class="pf-hero">
            <img v-if="profileUser.profile?.cover_image" :src="resolveStorageUrl(profileUser.profile.cover_image)" class="pf-cover" alt="" />
            <div v-else class="pf-cover pf-cover-empty"></div>
            <img v-if="profileUser.profile?.avatar" :src="resolveStorageUrl(profileUser.profile.avatar)" class="pf-avatar" alt="" />
            <span v-else class="pf-avatar pf-avatar-fallback">{{ (profileUser.name || '?')[0] }}</span>
            <h2 class="pf-name">{{ profileUser.name }}</h2>
          </div>

          <RouterLink v-if="profileUser.id" class="pf-view" :to="`/users/${profileUser.id}`">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            {{ t('chat.viewProfile') }}
          </RouterLink>

          <div class="pf-section">
            <h3 class="pf-section-title">{{ t('chat.details') }}</h3>
            <div class="pf-row">
              <span class="pf-key">{{ t('chat.name') }}</span>
              <span class="pf-val">{{ profileUser.name }}</span>
            </div>
            <div v-if="profileUser.email" class="pf-row">
              <span class="pf-key">{{ t('chat.email') }}</span>
              <a :href="`mailto:${profileUser.email}`" class="pf-val pf-link">{{ profileUser.email }}</a>
            </div>
            <div v-if="profileUser.profile?.phone" class="pf-row">
              <span class="pf-key">{{ t('auth.phone') }}</span>
              <a :href="`tel:${profileUser.profile.phone}`" class="pf-val pf-link">{{ profileUser.profile.phone }}</a>
            </div>
            <div v-if="profileUser.profile?.birth_date" class="pf-row">
              <span class="pf-key">{{ t('chat.birthday') }}</span>
              <span class="pf-val">{{ formatBirthday(profileUser.profile.birth_date) }}</span>
            </div>
            <div v-if="profileUser.created_at" class="pf-row">
              <span class="pf-key">{{ t('chat.memberSince') }}</span>
              <span class="pf-val">{{ formatJoined(profileUser.created_at) }}</span>
            </div>
            <div v-for="s in socialLinks" :key="s.label" class="pf-row">
              <span class="pf-key">{{ s.label }}</span>
              <a :href="s.url" target="_blank" rel="noopener" class="pf-val pf-link">{{ s.host }}</a>
            </div>
          </div>

          <div v-if="mediaFiles.length" class="pf-section">
            <h3 class="pf-section-title">{{ t('chat.mediaLinks') }}</h3>
            <p class="pf-media-count">{{ mediaFiles.length }} {{ t('chat.photos') }}</p>
            <div class="pf-media-grid">
              <a
                v-for="(img, i) in mediaFiles.slice(0, 6)"
                :key="img.image + i"
                :href="`${STORAGE_URL}/storage/${img.image}`"
                target="_blank"
                rel="noopener"
                class="pf-media-item"
                :class="{ 'pf-media-more': i === 5 && mediaFiles.length > 6 }"
              >
                <img :src="`${STORAGE_URL}/storage/${img.image}`" alt="" />
                <span v-if="i === 5 && mediaFiles.length > 6" class="pf-media-more-label">+{{ mediaFiles.length - 6 }}</span>
              </a>
            </div>
          </div>

          <div class="pf-section pf-actions">
            <button class="pf-block" @click="doBlockFromProfile">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
              </svg>
              {{ t('chat.blockUser') }}
            </button>
          </div>
        </div>
        </aside>

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
                <img v-if="u.profile?.avatar" :src="resolveStorageUrl(u.profile.avatar)" class="user-result-avatar" alt="" />
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

    <!-- Forward message picker -->
    <Teleport to="body">
      <div v-if="showForwardPicker" class="modal-overlay" @click.self="showForwardPicker = false">
        <div class="modal-card">
          <div class="modal-header">
            <h3>{{ t('chat.forwardTo') }}</h3>
            <button class="modal-close" @click="showForwardPicker = false">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <div class="user-search-results">
              <div v-if="!visibleConversations.length" class="search-empty">{{ t('chat.noConversations') }}</div>
              <div
                v-for="conv in visibleConversations.filter(c => c.id !== activeConversation?.id)"
                :key="conv.id"
                class="user-result"
                @click="doForwardTo(conv)"
              >
                <img v-if="otherUser(conv).profile?.avatar" :src="resolveStorageUrl(otherUser(conv).profile.avatar)" class="user-result-avatar" alt="" />
                <span v-else class="user-result-avatar user-result-fallback">{{ (otherUser(conv).name || '?')[0] }}</span>
                <div>
                  <div class="user-result-name">{{ otherUser(conv).name }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Context menu -->
    <Teleport to="body">
      <div v-if="menuConversation" class="cm-backdrop" @click.self="closeMenu"></div>

        <div
          v-if="menuConversation"
          class="cm-card"
          :style="menuPos ? { top: `${menuPos.top}px`, left: `${menuPos.left}px` } : undefined"
          role="menu"
        >
          <div class="cm-handle"></div>
          <div class="cm-header">
            <span class="cm-title">{{ otherUser(menuConversation).name }}</span>
          </div>
          <div class="cm-group">
            <button class="cm-action" role="menuitem" @click="doMarkUnread">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 6l-10 7L2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/>
              </svg>
              <span>{{ t('chat.markUnread') }}</span>
            </button>
            <button class="cm-action" role="menuitem" @click="doTogglePin">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 17v5"/><path d="M5 3h14v5a4 4 0 0 1-4 4h-6a4 4 0 0 1-4-4z"/>
              </svg>
              <span>{{ menuConversation.is_pinned ? t('chat.unpin') : t('chat.pin') }}</span>
            </button>
            <button class="cm-action" role="menuitem" @click="doToggleMute">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M23 9l-6 6"/><path d="M17 9l6 6"/>
              </svg>
              <span>{{ menuConversation.is_muted ? t('chat.unmute') : t('chat.mute') }}</span>
            </button>
            <button class="cm-action" role="menuitem" @click="doArchive">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>
              </svg>
              <span>{{ isArchivedView ? t('chat.unarchive') : t('chat.archive') }}</span>
            </button>
          </div>
          <div class="cm-group cm-danger">
            <button class="cm-action" role="menuitem" @click="doDelete">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>
              </svg>
              <span>{{ t('chat.delete') }}</span>
            </button>
            <button class="cm-action" role="menuitem" @click="doBlock">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
              </svg>
              <span>{{ t('chat.block') }}</span>
            </button>
          </div>
        </div>

    </Teleport>

    <!-- Message action menu -->
    <Teleport to="body">
      <div v-if="menuMessage" class="mm-backdrop" @click.self="closeMessageMenu"></div>

        <div
          v-if="menuMessage"
          class="cm-card mm-card"
          :style="menuMsgPos ? { top: `${menuMsgPos.top}px`, left: `${menuMsgPos.left}px` } : undefined"
          role="menu"
        >
          <div class="cm-group">
            <button class="cm-action" role="menuitem" @click="doReply">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
              <span>{{ t('chat.reply') }}</span>
            </button>
            <button class="cm-action" role="menuitem" @click="doCopy">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
              </svg>
              <span>{{ t('chat.copy') }}</span>
            </button>
            <button class="cm-action" role="menuitem" @click="doForward">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 3 11 14"/><path d="M22 3L11 14"/><path d="M5 3h14l-3 9l-9 3L14 22H5l4 3L7 15"/>
              </svg>
              <span>{{ t('chat.forward') }}</span>
            </button>
            <button v-if="menuMessage.user_id === userId && !menuMessage.deleted_at" class="cm-action" role="menuitem" @click="doEditMsg">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
              </svg>
              <span>{{ t('chat.edit') }}</span>
            </button>
            <button class="cm-action" role="menuitem" @click="doTogglePinMsg">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 17v5"/><path d="M5 3h14v5a4 4 0 0 1-4 4h-6a4 4 0 0 1-4-4z"/>
              </svg>
              <span>{{ menuMessage.is_pinned ? t('chat.unpin') : t('chat.pin') }}</span>
            </button>
          </div>
          <div class="cm-group cm-danger">
            <button v-if="menuMessage.user_id === userId || !menuMessage.deleted_at" class="cm-action" role="menuitem" @click="doDeleteMsg">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>
              </svg>
              <span>{{ t('chat.delete') }}</span>
            </button>
          </div>
        </div>

    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { t } from '../i18n'
import { getConversations, createConversation, getMessages, sendMessage, searchUsers, updateConversation, deleteConversation, markConversationUnread, blockConversationUser, updateMessage, deleteMessage, pinMessage } from '../services/chat'
import { getProduct } from '../services/products'
import { STORAGE_URL, resolveStorageUrl } from '../services/http'
import { getEcho } from '../services/echo'
import { setChatUnread } from '../stores/chat'
import { pushToast } from '../stores/toast'

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
const isRecording = ref(false)
const draftProduct = ref(null)
const loading = ref(true)
const loadingMessages = ref(false)
const showNewChat = ref(false)
const userSearch = ref('')
const searchResults = ref([])
const searchingUsers = ref(false)
const sidebarOpen = ref(true)
const messagesContainer = ref(null)
const msgInput = ref(null)
const menuConversation = ref(null)
const menuPos = ref(null)
const isArchivedView = ref(false)
const menuMessage = ref(null)
const menuMsgPos = ref(null)
const replyTo = ref(null)
const editingMessage = ref(null)
const showForwardPicker = ref(false)
const forwardTargetMessage = ref(null)
const showProfileInfo = ref(false)
let subscribedChannelId = null
let replyHighlightTimer = null

const activeId = computed(() => activeConversation.value?.id)
const canSend = computed(() => newMessage.value.trim() || imagePreview.value || draftProduct.value)
const visibleConversations = computed(() => conversations.value.filter(c => c.last_message))

const draftImage = computed(() => {
  const p = draftProduct.value
  if (!p?.images?.length) return ''
  const img = p.images.find(i => i.is_primary) || p.images[0]
  return img ? `${STORAGE_URL}/storage/${img.image}` : ''
})

function otherUser(conv) {
  if (!conv) return {}
  return conv.user1_id === userId.value ? conv.user2 : conv.user1
}

const profileUser = computed(() => otherUser(activeConversation.value))

const mediaFiles = computed(() => (messages.value || []).filter(m => m.image && !m.deleted_at))

const socialLinks = computed(() => {
  const p = profileUser.value?.profile || {}
  const raw = [
    { label: t('auth.facebook'), url: p.facebook },
    { label: t('auth.instagram'), url: p.instagram },
    { label: t('auth.twitter'), url: p.twitter },
  ]
  return raw
    .filter(s => s.url)
    .map(s => {
      const url = /^https?:\/\//i.test(s.url) ? s.url : `https://${s.url}`
      let host = url.replace(/^https?:\/\//i, '').replace(/^www\./, '')
      try {
        host = new URL(url).hostname.replace(/^www\./, '')
      } catch {}
      return { label: s.label, url, host }
    })
})

function formatJoined(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  if (isNaN(d)) return dateStr
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long' })
}

function formatBirthday(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  if (isNaN(d)) return dateStr
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(async () => {
  try {
    const data = await getConversations()
    conversations.value = data.data || []

    const convId = Number(route.query.conversation)
    const draftProductId = Number(route.query.product)
    let conv = convId ? conversations.value.find(c => c.id === convId) : null
    if (!conv && convId && route.query.seller) {
      try {
        const data = await createConversation(Number(route.query.seller))
        conv = data.data
        if (conv) {
          conversations.value.unshift(conv)
        }
      } catch {}
    }
    if (conv) {
      activeConversation.value = conv
    }
    if (draftProductId) {
      await loadDraftProduct(draftProductId)
    }
    const query = { ...route.query }
    delete query.product
    delete query.conversation
    delete query.seller
    router.replace({ query })
  } finally {
    loading.value = false
  }
})

watch(activeConversation, (conv, prev) => {
  if (!conv) {
    showProfileInfo.value = false
    sidebarOpen.value = true
    return
  }
  showProfileInfo.value = false
  sidebarOpen.value = false
  if (prev == null) {
    history.pushState({ trinityChat: true }, '')
  }
  replyTo.value = null
  editingMessage.value = null
  newMessage.value = ''
  removeImage()
  loadMessages(conv.id)
  subscribeToChat(conv.id)
})

function handlePopState() {
  if (activeConversation.value) {
    activeConversation.value = null
    sidebarOpen.value = true
    showNewChat.value = false
  }
}

function handleShowList() {
  activeConversation.value = null
  sidebarOpen.value = true
  showNewChat.value = false
}

function computeChatUnread() {
  const activeId = activeConversation.value?.id
  let total = 0
  for (const conv of conversations.value) {
    if (conv.id === activeId) continue
    total += conv.unread_count || 0
  }
  setChatUnread(total)
}

watch(conversations, computeChatUnread, { deep: true })

function handleChatUnread(event) {
  const payload = event.detail
  if (!payload?.conversation_id || !payload.message) return

  const conversation = conversations.value.find(c => c.id === payload.conversation_id)
  if (!conversation) {
    reloadConversations()
    return
  }

  conversation.last_message = payload.message
  conversation.last_message_at = payload.message.created_at

   if (activeId.value !== payload.conversation_id) {
      conversation.unread_count = (conversation.unread_count || 0) + 1
      notifyChatMessage(conversation, payload.message)
   }
   }

function notifyChatMessage(conversation, message) {
   const who = otherUser(conversation)?.name || ''

   let preview = message?.body || ''
   if (!preview && message?.image) preview = t('toast.sentImage')
   if (!preview && message?.voice) preview = t('toast.sentVoice')

   pushToast({
      type: 'info',
      title: t('toast.newMessage'),
      message: who ? `${who}: ${preview}`.trim() : preview,
      action: t('toast.openChat'),
      onAction: () => selectConversation(conversation),
   })
   }

onMounted(() => {
  window.addEventListener('popstate', handlePopState)
  window.addEventListener('chat-show-list', handleShowList)
  window.addEventListener('chat-unread', handleChatUnread)
})

onUnmounted(() => {
  clearTimeout(replyHighlightTimer)
  window.removeEventListener('popstate', handlePopState)
  window.removeEventListener('chat-show-list', handleShowList)
  window.removeEventListener('chat-unread', handleChatUnread)
  const echo = getEcho()
  if (echo && subscribedChannelId) {
    echo.leave(`chat.${subscribedChannelId}`)
  }
})

async function loadMessages(conversationId) {
  loadingMessages.value = true
  try {
    const data = await getMessages(conversationId)
    messages.value = data.data?.data || []
    const conv = conversations.value.find(c => c.id === conversationId)
    if (conv) {
      conv.unread_count = 0
    }
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
    .listen('.message.updated', (event) => {
      const index = messages.value.findIndex(m => m.id === event.message.id)
      if (index !== -1) messages.value[index] = event.message
    })
}

function selectConversation(conv) {
  clearDraftProduct()
  activeConversation.value = conv
}

async function send() {
  if (!canSend.value || !activeConversation.value) return

  const body = newMessage.value.trim()
  const file = imageFile.value
  const productDraft = draftProduct.value

  if (editingMessage.value) {
    const editing = editingMessage.value
    editingMessage.value = null
    if (!body) return
    try {
      const data = await updateMessage(editing.id, { body })
      replaceMessage(data.data)
    } catch {
      editingMessage.value = editing
    }
    return
  }

  const repliedTo = replyTo.value

  newMessage.value = ''
  removeImage()
  clearDraftProduct()
  replyTo.value = null

  try {
    let data
    if (file) {
      const formData = new FormData()
      if (body) formData.append('body', body)
      formData.append('image', file)
      if (productDraft) formData.append('product_id', productDraft.id)
      if (repliedTo) formData.append('replied_to', repliedTo.id)
      data = await sendMessage(activeConversation.value.id, formData)
    } else {
      const payload = {}
      if (body) payload.body = body
      if (productDraft) payload.product_id = productDraft.id
      if (repliedTo) payload.replied_to = repliedTo.id
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
    if (productDraft) draftProduct.value = productDraft
    if (repliedTo) replyTo.value = repliedTo
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

function toggleVoice() {
  isRecording.value = !isRecording.value
}

async function loadDraftProduct(productId) {
  try {
    const data = await getProduct(productId)
    draftProduct.value = data.data || null
  } catch {
    draftProduct.value = null
  }
}

function clearDraftProduct() {
  draftProduct.value = null
}

function openMessageMenu(msg, event) {
  menuMessage.value = msg
  const el = event?.currentTarget
  if (!el) {
    menuMsgPos.value = null
    return
  }
  const rect = el.getBoundingClientRect()
  const menuWidth = 236
  const menuHeight = 300
  const margin = 8

  let top = rect.top + rect.height / 2 - menuHeight / 2
  let left = msg.user_id === userId.value
    ? rect.left - menuWidth - 6
    : rect.right + 6

  top = Math.max(margin, Math.min(top, window.innerHeight - menuHeight - margin))
  left = Math.max(margin, Math.min(left, window.innerWidth - menuWidth - margin))
  menuMsgPos.value = { top, left }
}

function closeMessageMenu() {
  menuMessage.value = null
  menuMsgPos.value = null
}

function replaceMessage(updated) {
  const index = messages.value.findIndex(m => m.id === updated.id)
  if (index !== -1) messages.value[index] = updated
}

function doReply() {
  const msg = menuMessage.value
  if (!msg) return
  replyTo.value = msg
  editingMessage.value = null
  closeMessageMenu()
  nextTick(() => msgInput.value?.focus())
}

function doCopy() {
  const msg = menuMessage.value
  closeMessageMenu()
  const body = msg?.body || ''
  if (body && navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(body)
  }
}

function doDeleteMsg() {
  const msg = menuMessage.value
  closeMessageMenu()
  if (!msg) return
  if (!window.confirm(t('chat.confirmDeleteMsg'))) return
  deleteMessage(msg.id)
    .then(res => replaceMessage(res.data))
    .catch(() => {})
}

function doTogglePinMsg() {
  const msg = menuMessage.value
  closeMessageMenu()
  if (!msg) return
  pinMessage(msg.id, !msg.is_pinned)
    .then(res => replaceMessage(res.data))
    .catch(() => {})
}

function doEditMsg() {
  const msg = menuMessage.value
  closeMessageMenu()
  if (!msg || msg.user_id !== userId.value || msg.deleted_at) return
  editingMessage.value = msg
  replyTo.value = null
  newMessage.value = msg.body || ''
  nextTick(() => msgInput.value?.focus())
}

function cancelComposer() {
  replyTo.value = null
  editingMessage.value = null
  newMessage.value = ''
}

function doForward() {
  forwardTargetMessage.value = menuMessage.value
  closeMessageMenu()
  showForwardPicker.value = true
}

async function doForwardTo(conv) {
  const msg = forwardTargetMessage.value
  showForwardPicker.value = false
  forwardTargetMessage.value = null
  if (!msg) return

  const payload = { forwarded: true }
  if (msg.body) payload.body = msg.body
  if (msg.product_id) payload.product_id = msg.product_id
  if (msg.image && msg.image.startsWith('chat-images/')) payload.image_path = msg.image

  try {
    const res = await sendMessage(conv.id, payload)
    if (activeConversation.value?.id === conv.id) {
      messages.value.push(res.data)
      nextTick(() => scrollToBottom())
    }
    const c = conversations.value.find(item => item.id === conv.id)
    if (c) {
      c.last_message = res.data
      c.last_message_at = res.data.created_at
    }
  } catch {
    // ignore
  }
}

function replyPreview(msg) {
  if (!msg) return ''
  if (msg.deleted_at) return t('chat.msgDeleted')
  if (msg.body) return msg.body
  if (msg.product) return msg.product.name
  if (msg.image) return t('chat.photo')
  return ''
}

function composerPreviewText() {
  if (editingMessage.value) return editingMessage.value.body || t('chat.photo')
  if (replyTo.value) return replyPreview(replyTo.value)
  return ''
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

function jumpToMessage(messageId) {
  const target = document.getElementById(`message-${messageId}`)
  if (!target) return

  target.scrollIntoView({ behavior: 'smooth', block: 'center' })
  target.classList.remove('is-reply-target')
  clearTimeout(replyHighlightTimer)

  requestAnimationFrame(() => {
    target.classList.add('is-reply-target')
  })

  replyHighlightTimer = window.setTimeout(() => {
    target.classList.remove('is-reply-target')
  }, 1600)
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

function openMenu(conv, event) {
  menuConversation.value = conv
  const el = event?.currentTarget
  if (!el) {
    menuPos.value = null
    return
  }
  const rect = el.getBoundingClientRect()
  const menuWidth = 292
  const menuHeight = 372
  const margin = 8

  let top = rect.bottom + 6
  let left = rect.right - menuWidth

  if (top + menuHeight > window.innerHeight - margin) {
    top = Math.max(margin, window.innerHeight - menuHeight - margin)
  }
  if (left < margin) {
    left = margin
  }
  if (left + menuWidth > window.innerWidth - margin) {
    left = window.innerWidth - menuWidth - margin
  }

  menuPos.value = { top, left }
}

function closeMenu() {
  menuConversation.value = null
  menuPos.value = null
}

async function reloadConversations() {
  try {
    const data = await getConversations(isArchivedView.value)
    conversations.value = data.data || []
    const active = activeConversation.value
    if (active) {
      activeConversation.value = conversations.value.find(c => c.id === active.id) || active
    }
  } catch {}
}

async function toggleArchivedView() {
  isArchivedView.value = !isArchivedView.value
  await reloadConversations()
}

async function doMarkUnread() {
  const conv = menuConversation.value
  closeMenu()
  if (!conv) return
  try {
    await markConversationUnread(conv.id)
    await reloadConversations()
  } catch {}
}

async function doTogglePin() {
  const conv = menuConversation.value
  closeMenu()
  if (!conv) return
  try {
    await updateConversation(conv.id, { is_pinned: !conv.is_pinned })
    await reloadConversations()
  } catch {}
}

async function doToggleMute() {
  const conv = menuConversation.value
  closeMenu()
  if (!conv) return
  try {
    await updateConversation(conv.id, { is_muted: !conv.is_muted })
    await reloadConversations()
  } catch {}
}

async function doArchive() {
  const conv = menuConversation.value
  closeMenu()
  if (!conv) return
  try {
    await updateConversation(conv.id, { is_archived: !conv.is_archived })
    if (activeConversation.value?.id === conv.id) {
      activeConversation.value = null
    }
    await reloadConversations()
  } catch {}
}

async function doDelete() {
  const conv = menuConversation.value
  closeMenu()
  if (!conv) return
  try {
    await deleteConversation(conv.id)
    if (activeConversation.value?.id === conv.id) {
      activeConversation.value = null
    }
    await reloadConversations()
  } catch {}
}

async function doBlock() {
  const conv = menuConversation.value
  closeMenu()
  if (!conv) return
  try {
    await blockConversationUser(conv.id)
    if (activeConversation.value?.id === conv.id) {
      activeConversation.value = null
    }
    await reloadConversations()
  } catch {}
}

async function doBlockFromProfile() {
  const conv = activeConversation.value
  showProfileInfo.value = false
  if (!conv) return
  try {
    await blockConversationUser(conv.id)
    activeConversation.value = null
    await reloadConversations()
  } catch {}
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
  height: calc(100dvh - 60px);
  overflow: hidden;
}

.sidebar-scrim {
  display: none;
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

}

.btn-new-chat:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.btn-archived {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;

}

.btn-archived:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.btn-archived.active {
  background: var(--accent-soft);
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
  padding: 0.85rem 0.5rem 0.85rem 1.25rem;
  cursor: pointer;
  border-bottom: 1px solid var(--border);

}

.conv-item:hover {
  background: var(--surface-2);
}

.conv-item.active {
  background: var(--accent-soft);
}

.conv-more {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  border: none;
  background: none;
  color: var(--text-muted);
  cursor: pointer;
  border-radius: var(--radius-sm);

  margin-right: 0.5rem;
}

.conv-more:hover {
  background: var(--surface-3, var(--surface-2));
  color: var(--text);
}

.conv-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.15rem;
}

.conv-right {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  flex-shrink: 0;
  margin-left: 0.5rem;
}

.conv-muted {
  color: var(--text-muted);
  flex-shrink: 0;
}

.conv-pinned {
  color: var(--accent);
  vertical-align: -1px;
  margin-right: 0.25rem;
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
  color: var(--on-primary);
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
  color: var(--on-primary);
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
  color: var(--on-primary);
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
  position: relative;
  scroll-margin-block: 1rem;
}

.message-row.mine {
  margin-left: auto;
  flex-direction: row-reverse;
}

.message-more-btn {
  display: inline-flex;
  width: 24px;
  height: 24px;
  flex: 0 0 auto;
  align-items: center;
  justify-content: center;
  align-self: flex-end;
  padding: 0;
  border: 0;
  border-radius: var(--radius-sm);
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  opacity: 0.7;
}

.message-more-btn:hover {
  background: var(--surface-2);
  color: var(--text);
  opacity: 1;
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
  color: var(--on-primary);
  font-size: 0.7rem;
  font-weight: 600;
}

.message-bubble {
  padding: 0.5rem 0.75rem;
  border-radius: var(--radius-md);
  background: var(--surface-2);
  border: 1px solid var(--border);
  max-width: 100%;
  position: relative;
}

.message-row.mine .message-bubble {
  background: var(--accent);
  color: var(--on-primary);
  border-color: var(--accent);
}

.message-row.is-reply-target .message-bubble {
  animation: reply-message-highlight 1.6s ease;
}

@keyframes reply-message-highlight {
  0%, 100% {
    box-shadow: var(--shadow-sm);
  }

  35% {
    box-shadow: 0 0 0 3px var(--accent-soft), var(--shadow-md);
  }
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

.msg-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.25rem;
  margin-top: 0.2rem;
}

.msg-time {
  font-size: 0.6rem;
  opacity: 0.6;
}

.msg-status {
  flex-shrink: 0;
}

.msg-status.sent {
  color: rgba(255, 255, 255, 0.7);
}

.msg-status.seen {
  color: #d1fae5;
}

.msg-pin-badge {
  position: absolute;
  top: -7px;
  right: -3px;
  color: var(--accent);
  display: flex;
}

.message-row.mine .msg-pin-badge {
  color: var(--on-primary);
}

.msg-reply-quote {
  display: flex;
  width: 100%;
  flex-direction: column;
  gap: 0.1rem;
  padding: 0.3rem 0.5rem;
  margin-bottom: 0.35rem;
  border: 0;
  border-left: 3px solid var(--accent);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;
}

.message-row.mine .msg-reply-quote {
  background: rgba(255, 255, 255, 0.18);
  border-left-color: rgba(255, 255, 255, 0.7);
}

.msg-reply-owner {
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--accent);
}

.message-row.mine .msg-reply-owner {
  color: var(--on-primary);
}

.msg-reply-text {
  font-size: 0.75rem;
  line-height: 1.3;
  color: var(--text-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 220px;
}

.message-row.mine .msg-reply-text {
  color: rgba(255, 255, 255, 0.85);
}

.msg-deleted-text {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-style: italic;
  opacity: 0.7;
  font-size: 0.9rem;
}

.msg-forwarded-label {
  display: block;
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--accent);
  margin-bottom: 0.15rem;
}

.message-row.mine .msg-forwarded-label {
  color: var(--on-primary);
}

.mm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 300;
  background: rgba(8, 10, 14, 0.22);
}

.mm-card {
  width: 236px;
}

.composer-preview {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.4rem 0.6rem;
  margin-bottom: 0.4rem;
  border-left: 3px solid var(--accent);
  border-radius: var(--radius-sm);
  background: var(--surface-2);
}

.composer-preview-content {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  min-width: 0;
}

.composer-preview-label {
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--accent);
}

.composer-preview-text {
  font-size: 0.78rem;
  color: var(--text-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.composer-preview-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 50%;
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;
  flex-shrink: 0;
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
  color: var(--on-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
}

.product-draft-wrap {
  position: relative;
  display: inline-block;
  padding: 0;
}

.msg-product-draft {
  margin-bottom: 0;
  position: relative;
}

.product-draft-label {
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--accent);
  display: block;
}

.product-draft-remove {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: var(--danger);
  color: var(--on-primary);
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
  color: var(--on-primary);
  cursor: pointer;
  flex-shrink: 0;

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
  color: var(--on-primary);
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
  color: var(--on-primary);
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

/* ---------- Context menu (glass) ---------- */
.cm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 300;
  background: rgba(8, 10, 14, 0.22);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}

.cm-card {
  position: fixed;
  z-index: 301;
  width: 292px;
  padding: 6px;
  border-radius: 18px;
  background: rgba(38, 40, 48, 0.72);
  backdrop-filter: blur(28px) saturate(180%);
  -webkit-backdrop-filter: blur(28px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.14);
  box-shadow:
    0 24px 60px -12px rgba(0, 0, 0, 0.55),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
  color: var(--on-primary);
  overflow: hidden;
}

.cm-handle {
  width: 30px;
  height: 4px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.22);
  margin: 4px auto 2px;
}

.cm-header {
  padding: 8px 10px 4px;
  text-align: center;
}

.cm-title {
  font-size: 0.8rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.72);
  letter-spacing: 0.01em;
  word-break: break-word;
}

.cm-group {
  display: flex;
  flex-direction: column;
  padding: 4px 0;
}

.cm-group + .cm-group {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.cm-action {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  padding: 10px 12px;
  border: none;
  background: transparent;
  color: var(--on-primary);
  font-size: 0.94rem;
  font-weight: 400;
  letter-spacing: 0.01em;
  text-align: left;
  cursor: pointer;
  border-radius: 11px;

  -webkit-tap-highlight-color: transparent;
}

.cm-action:hover {
  background: rgba(255, 255, 255, 0.1);
}

.cm-action svg {
  flex-shrink: 0;
}

.cm-danger .cm-action {
  color: #ff6c5f;
}

.cm-danger .cm-action:hover {
  background: rgba(255, 108, 95, 0.12);
}

/* ---------- Responsive ---------- */
@media (max-width: 1180px) {
  .chat-sidebar {
    width: 320px;
  }
}

@media (max-width: 1300px), (hover: none) and (pointer: coarse) {
  .chat-page {
    position: relative;
    flex-direction: column;
    height: calc(100vh - 70px);
    height: calc(100dvh - 70px);
  }

  .sidebar-scrim {
    display: block;
    position: fixed;
    inset: 0;
    z-index: 15;
    background: rgba(0, 0, 0, 0.35);
  }

  .chat-sidebar {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 100%;
    z-index: 20;
    transform: translateX(-100%);
    border-right: none;
    box-shadow: var(--shadow-lg);
  }

  .chat-sidebar.open {
    transform: translateX(0);
  }

  .chat-main {
    width: 100%;
  }

  .back-btn {
    display: flex;
  }

  .message-row {
    max-width: 88%;
  }
}

@media (max-width: 768px) {
  .chat-page {
    height: calc(100vh - 60px - 52px);
    height: calc(100dvh - 60px - 52px);
  }
}

@media (max-width: 560px) {
  .sidebar-header {
    padding: 0.75rem 0.85rem;
  }

  .sidebar-title {
    font-size: 1.1rem;
  }

  .conv-item {
    gap: 0.6rem;
    padding: 0.7rem 0.4rem 0.7rem 0.85rem;
  }

  .conv-avatar {
    width: 40px;
    height: 40px;
  }

  .chat-header {
    padding: 0.6rem 0.7rem;
  }

  .messages-area {
    padding: 0.7rem;
  }

  .message-row {
    max-width: 92%;
  }

  .msg-avatar {
    width: 24px;
    height: 24px;
  }

  .msg-product-card {
    max-width: 220px;
  }

  .chat-input-area {
    padding: 0.6rem 0.7rem;
  }

  .cm-card {
    width: min(292px, calc(100vw - 24px));
  }

  .cm-action {
    padding: 11px 10px;
  }
}
</style>

<style scoped>
.chat-page {
  background: var(--bg);
  -webkit-tap-highlight-color: transparent;
}

.chat-sidebar {
  background: var(--surface);
  border-right-color: var(--border);
  overscroll-behavior: contain;
}

.sidebar-header {
  padding: 0.85rem 1rem;
  justify-content: flex-start;
  gap: 0.5rem;
  flex-shrink: 0;
}

.sidebar-title {
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.btn-archived,
.btn-new-chat {
  width: 38px;
  height: 38px;
  padding: 0;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: var(--text-muted);

}

.btn-archived:hover,
.btn-new-chat:hover {
  background: var(--surface-2);
  color: var(--text);
}

.btn-archived.active {
  background: var(--accent-soft);
  color: var(--accent);
}

.btn-archived {
  position: relative;
  margin-left: auto;
}

.btn-archived::after {
  content: '';
  position: absolute;
  top: 50%;
  right: -1px;
  transform: translateY(-50%);
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: transparent;

}

.btn-archived.active::after {
  background: var(--accent);
}

.conversation-list,
.messages-area {
  scrollbar-width: thin;
  scrollbar-color: var(--border-strong) transparent;
}

.conversation-list {
  min-height: 0;
  flex: 1 1 0;
}

.conv-item {
  gap: 11px;
  padding: 9px 10px 9px 14px;
  margin: 1px 8px;
  border-bottom: none;
  border-radius: 12px;
}

.conv-item:hover {
  background: var(--surface-2);
}

.conv-item.active {
  background: var(--accent-soft);
  box-shadow: inset 3px 0 0 var(--primary);
}

.conv-avatar {
  width: 52px;
  height: 52px;
}

.conv-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
}

.conv-time {
  font-size: 0.7rem;
  color: var(--text-muted);
}

.conv-preview {
  font-size: 0.82rem;
  color: var(--text-muted);
}

.conv-item:has(.conv-badge) .conv-preview {
  color: var(--text);
  font-weight: 600;
}

.conv-item:has(.conv-badge) .conv-name {
  color: var(--text);
}

.conv-badge {
  min-width: 20px;
  height: 20px;
  line-height: 20px;
  padding: 0 6px;
  border-radius: 999px;
  background: var(--accent);
  color: var(--on-primary);
  font-size: 0.7rem;
  font-weight: 600;
}

.conv-right {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.conv-more {
  opacity: 1;

}

.conv-more:hover {
  background: var(--surface-3);
  color: var(--text);
}

.conv-avatar-fallback,
.chat-header-fallback,
.msg-avatar-fallback {
  background: var(--primary);
  color: var(--on-primary);
  font-weight: 600;
}

.chat-main {
  background: var(--bg);
  min-height: 0;
  min-width: 0;
  overflow: hidden;
}

.chat-header {
  background: var(--navbar-bg);
  border-bottom-color: var(--border);
  padding: 0.7rem 1rem;
  flex-shrink: 0;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.chat-header-name {
  font-size: 1rem;
  font-weight: 600;
}

.messages-area {
  position: relative;
  min-height: 0;
  flex: 1 1 0;
  padding: 1.1rem 1.25rem;
  gap: 0.6rem;
}

.messages-area::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: url('/trinity-logo.svg');
  background-repeat: no-repeat;
  background-position: center;
  background-size: min(380px, 60vw);
  opacity: 0.1;
  pointer-events: none;
}

.message-bubble {
  background: var(--surface);
  border-color: var(--border);
  color: var(--text);
  border-radius: 18px;
  border-bottom-left-radius: 4px;
  box-shadow: var(--shadow-sm);
  padding: 0.55rem 0.8rem;
}

.message-row.mine .message-bubble {
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent-dark) 100%);
  border-color: transparent;
  color: var(--on-primary);
  border-radius: 18px;
  border-bottom-left-radius: 18px;
  border-bottom-right-radius: 4px;
  box-shadow: 0 10px 24px -16px var(--focus-ring);
}

.msg-time {
  font-size: 0.64rem;
  color: var(--text-muted);
}

.message-row.mine .msg-time {
  color: rgba(255, 255, 255, 0.85);
}

.msg-status.sent {
  color: var(--text-muted);
}

.msg-status.seen {
  color: var(--success);
}

.message-row.mine .msg-status.sent {
  color: color-mix(in srgb, var(--on-primary) 72%, transparent);
}

.message-row.mine .msg-status.seen {
  color: var(--on-primary);
}

.msg-forwarded-label,
.msg-reply-owner {
  color: var(--primary);
}

.message-row.mine .msg-forwarded-label {
  color: rgba(255, 255, 255, 0.95);
}

.message-row.mine .msg-reply-owner {
  color: var(--on-primary);
}

.msg-avatar {
  width: 26px;
  height: 26px;
}

.msg-image,
.msg-product-card {
  border-radius: 12px;
}

.message-row.mine .msg-product-card {
  background: rgba(255, 255, 255, 0.14);
  border-color: rgba(255, 255, 255, 0.22);
}

.msg-product-price {
  color: var(--accent);
}

.message-row.mine .msg-product-price {
  color: var(--on-primary);
}

.chat-input-area {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  background: var(--navbar-bg);
  border-top-color: var(--border);
  padding: 0.6rem 0.85rem 0.85rem;
  flex-shrink: 0;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}

.chat-input-area::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: url('/trinity-logo.svg');
  background-repeat: no-repeat;
  background-position: center;
  background-size: min(260px, 55vw);
  opacity: 0.08;
  pointer-events: none;
}

.chat-input-area > * {
  position: relative;
  z-index: 1;
}

.img-upload-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: var(--text-muted);
  margin-bottom: 0;

}

.img-upload-btn:hover {
  background: var(--surface-2);
  color: var(--text);
}

.msg-form {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.msg-input {
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface);
  color: var(--text);
  padding: 0.62rem 1.05rem;
  font-size: 0.94rem;
}

.msg-input::placeholder {
  color: var(--text-muted);
}

.msg-input:focus {
  border-color: var(--primary);
  background: var(--surface);
  box-shadow: 0 0 0 3px var(--focus-ring);
}

.send-btn {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--accent-dark));
  color: var(--on-primary);
}

.send-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, var(--primary), var(--accent-dark));
  box-shadow: 0 4px 14px var(--focus-ring);
  transform: none;
}

.composer-preview {
  border-left: none;
  background: var(--surface-2);
  border-radius: 12px;
}

.cm-backdrop,
.mm-backdrop {
  background: var(--overlay);
}

.cm-card {
  background: var(--navbar-bg);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  backdrop-filter: blur(24px) saturate(160%);
  -webkit-backdrop-filter: blur(24px) saturate(160%);
  color: var(--text);
}

.cm-handle {
  background: var(--border-strong);
}

.cm-title {
  color: var(--text-muted);
}

.cm-group + .cm-group {
  border-top-color: var(--border);
}

.cm-action {
  color: var(--text);
}

.cm-action:hover {
  background: var(--surface-2);
}

.cm-danger .cm-action {
  color: var(--danger);
}

.cm-danger .cm-action:hover {
  background: var(--danger-soft);
}

.modal-overlay {
  background: var(--overlay);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

.modal-card {
  background: var(--surface);
  border: 1px solid var(--border);
}

.modal-card .input {
  background: var(--surface-2);
  border-color: var(--border);
  color: var(--text);
}

.modal-card .input::placeholder {
  color: var(--text-muted);
}

.modal-card .input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-soft);
}

.chat-placeholder p {
  font-size: 0.9rem;
}

@media (max-width: 1300px), (hover: none) and (pointer: coarse) {
  .chat-sidebar {
    background: var(--surface);
  }

  .sidebar-scrim {
    background: var(--overlay);
  }
}

@media (max-width: 560px) {
  .conv-avatar {
    width: 46px;
    height: 46px;
  }

  .conv-item {
    padding: 8px 8px 8px 12px;
  }

  .conv-name {
    font-size: 0.9rem;
  }

  .conv-preview {
    font-size: 0.78rem;
  }

  .messages-area {
    padding: 0.8rem 0.85rem;
  }

  .chat-input-area {
    padding: 0.55rem 0.7rem 0.7rem;
  }
}
</style>

<style scoped>
/* Messenger-style Details panel */
.chat-page {
  flex: 1 1 auto;
  min-height: 0;
  height: auto;
}

.chat-header-user {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex: 1;
  min-width: 0;
  padding: 0;
  border: none;
  background: none;
  cursor: pointer;
  text-align: left;
}

.chat-header-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chat-header-details {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;

}

.chat-header-details:hover {
  background: var(--surface-2);
}

.voice-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  flex-shrink: 0;

}

.voice-btn:hover {
  background: var(--surface-2);
  color: var(--text);
}

.voice-btn.recording {
  color: var(--on-primary);
  background: var(--danger);
}

.pf-scrim {
  display: none;
}

.profile-info {
  width: 340px;
  min-width: 340px;
  height: 100%;
  background: var(--surface);
  border-left: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.pf-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.7rem 1rem;
  border-bottom: 1px solid var(--border);
  background: var(--navbar-bg);
  flex-shrink: 0;
}

.pf-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.pf-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface-2);
  color: var(--text-muted);
  cursor: pointer;
}

.pf-close:hover {
  border-color: var(--border-strong);
  background: var(--surface-3);
  color: var(--text);
}

.pf-scroll {
  flex: 1 1 0;
  min-height: 0;
  overflow-y: auto;
}

.pf-hero {
  position: relative;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}

.pf-cover {
  width: 100%;
  height: 130px;
  object-fit: cover;
  display: block;
}

.pf-cover-empty {
  background: linear-gradient(135deg, var(--primary), var(--info));
}

.pf-avatar {
  width: 84px;
  height: 84px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--surface);
  margin: -44px auto 0;
  display: block;
  position: relative;
  z-index: 1;
  font-size: 2.2rem;
  text-transform: uppercase;
}

.pf-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: var(--on-primary);
  background: var(--primary);
}

.pf-name {
  text-align: center;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text);
  margin: 0.6rem 0.5rem 0;
}

.pf-view {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0.85rem auto;
  padding: 0.5rem 0.9rem;
  width: fit-content;
  border: 1px solid color-mix(in srgb, var(--primary) 24%, transparent);
  border-radius: 999px;
  background: var(--accent-soft);
  color: var(--primary);
  font-weight: 600;
  font-size: 0.9rem;
  text-decoration: none;
}

.pf-view:hover {
  border-color: var(--primary);
  background: color-mix(in srgb, var(--primary) 16%, var(--surface));
  color: var(--primary);
}

.pf-section {
  padding: 0.85rem 1rem;
  border-top: 8px solid var(--surface-2);
}

.pf-section-title {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  font-weight: 700;
  margin: 0 0 0.35rem;
}

.pf-row {
  display: flex;
  gap: 0.75rem;
  padding: 0.45rem 0;
  align-items: baseline;
}

.pf-key {
  width: 88px;
  flex: 0 0 88px;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.pf-val {
  flex: 1;
  min-width: 0;
  color: var(--text);
  font-size: 0.92rem;
  overflow-wrap: anywhere;
}

.pf-link {
  color: var(--primary);
  text-decoration: none;
}

.pf-link:hover {
  text-decoration: underline;
}

.pf-media-count {
  margin: 0 0 0.6rem;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.pf-media-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

.pf-media-item {
  position: relative;
  display: block;
  aspect-ratio: 1;
  overflow: hidden;
  border-radius: 6px;
}

.pf-media-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.pf-media-more::after {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
}

.pf-media-more-label {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--on-primary);
  font-weight: 700;
  font-size: 1.1rem;
  z-index: 1;
}

.pf-actions {
  border-top: 8px solid var(--surface-2);
}

.pf-block {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.6rem;
  border: 1px solid color-mix(in srgb, var(--danger) 24%, var(--border));
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--danger);
  font-weight: 600;
  cursor: pointer;
}

.pf-block:hover {
  border-color: var(--danger);
  background: var(--danger-soft);
}

@media (max-width: 900px) {
  .pf-scrim {
    display: block;
    position: fixed;
    inset: 0;
    z-index: 90;
    background: var(--overlay);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
  }

  .profile-info {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 100;
    width: 100%;
    min-width: 0;
    border-left: none;
    box-shadow: var(--shadow-lg);
  }

  .pf-cover {
    height: 180px;
  }
}
</style>
