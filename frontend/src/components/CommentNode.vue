<template>
  <div class="comment-item" :class="{ 'reply-item': depth > 0 }">
    <div class="comment-header">
      <RouterLink :to="`/users/${comment.user_id}`" class="comment-user">
        <img v-if="comment.user?.profile?.avatar" :src="comment.user.profile.avatar" class="comment-avatar" alt="" />
        <span v-else class="comment-avatar comment-avatar-fallback">{{ (comment.user?.name || '?')[0] }}</span>
        <span class="comment-author">{{ comment.user?.name }}</span>
      </RouterLink>
      <span class="comment-time">{{ formatTime(comment.created_at) }}</span>
    </div>
    <p class="comment-body">{{ comment.body }}</p>

    <div class="comment-actions">
      <button v-if="isAuthenticated" class="comment-reply-btn" @click="$emit('set-reply', comment.id)">
        {{ t('product.reply') }}
      </button>
      <button v-if="isOwner || comment.user_id === currentUserId" class="comment-delete-btn" @click="$emit('delete-comment', comment.id)">
        {{ t('product.delete') }}
      </button>
    </div>

    <div v-if="replyTo === comment.id" class="reply-form">
      <textarea
        :value="replyBody"
        @input="$emit('update-reply-body', $event.target.value)"
        class="comment-input reply-input"
        :placeholder="t('product.writeReply')"
        rows="2"
      ></textarea>
      <button
        class="btn btn-primary btn-sm"
        :disabled="!replyBody.trim() || postingComment"
        @click="$emit('post-reply', comment.id)"
      >{{ t('product.postComment') }}</button>
    </div>

    <div v-if="comment.replies?.length" class="replies">
      <CommentNode
        v-for="reply in comment.replies"
        :key="reply.id"
        :comment="reply"
        :depth="depth + 1"
        :max-depth="maxDepth"
        :is-authenticated="isAuthenticated"
        :current-user-id="currentUserId"
        :is-owner="isOwner"
        :reply-to="replyTo"
        :reply-body="replyBody"
        :posting-comment="postingComment"
        @set-reply="(id) => $emit('set-reply', id)"
        @update-reply-body="(val) => $emit('update-reply-body', val)"
        @post-reply="(id) => $emit('post-reply', id)"
        @delete-comment="(id) => $emit('delete-comment', id)"
      />
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { t } from '../i18n'

defineProps({
  comment: { type: Object, required: true },
  depth: { type: Number, default: 0 },
  maxDepth: { type: Number, default: 3 },
  isAuthenticated: { type: Boolean, default: false },
  currentUserId: { type: Number, default: null },
  isOwner: { type: Boolean, default: false },
  replyTo: { type: Number, default: null },
  replyBody: { type: String, default: '' },
  postingComment: { type: Boolean, default: false },
})

defineEmits(['set-reply', 'update-reply-body', 'post-reply', 'delete-comment'])

function formatTime(dateStr) {
  const now = new Date()
  const date = new Date(dateStr)
  const diff = Math.floor((now - date) / 1000)

  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h`
  if (diff < 604800) return `${Math.floor(diff / 86400)}d`
  return date.toLocaleDateString()
}
</script>

<style scoped>
.comment-item {
  padding: 1rem 0;
  border-top: 1px solid var(--border);
}

.comment-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.4rem;
}

.comment-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: inherit;
}

.comment-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}

.comment-avatar-fallback {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
  width: 28px;
  height: 28px;
  border-radius: 50%;
}

.comment-author {
  font-weight: 600;
  font-size: 0.88rem;
  color: var(--text);
}

.comment-time {
  font-size: 0.78rem;
  color: var(--text-muted);
}

.comment-body {
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0.3rem 0;
  color: var(--text);
}

.comment-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.3rem;
}

.comment-reply-btn,
.comment-delete-btn {
  background: none;
  border: none;
  font: inherit;
  font-size: 0.78rem;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0;
  transition: color 0.15s;
}

.comment-reply-btn:hover {
  color: var(--accent);
}

.comment-delete-btn:hover {
  color: var(--danger);
}

.reply-form {
  margin-top: 0.75rem;
}

.comment-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font: inherit;
  font-size: 0.9rem;
  color: var(--text);
  background: var(--surface);
  resize: vertical;
  min-height: 60px;
  margin-bottom: 0.5rem;
  transition: border-color 0.15s;
}

.comment-input:focus {
  outline: none;
  border-color: var(--accent);
}

.reply-input {
  min-height: 48px;
  font-size: 0.85rem;
}

.replies {
  margin-top: 0.75rem;
  padding-left: 1.5rem;
  border-left: 2px solid var(--border);
}

.reply-item {
  padding: 0.75rem 0;
  border-top: none;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font: inherit;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: opacity 0.15s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--accent);
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-sm {
  padding: 0.4rem 0.85rem;
  font-size: 0.82rem;
}
</style>
