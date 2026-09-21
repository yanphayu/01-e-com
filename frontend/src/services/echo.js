import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { API_URL } from './http'

window.Pusher = Pusher

// Echo's channel-auth POST must hit the root /broadcasting/auth route, which
// lives OUTSIDE the /api prefix (Broadcast::routes() registers it at root).
// API_URL already ends in /api → strip it for the auth call, same as STORAGE_URL.
const ECHO_AUTH_URL = API_URL.replace(/\/api\/?$/, '') + '/broadcasting/auth'

let echo = null
let echoUserId = null

export function initEcho(userId) {
  const token = localStorage.getItem('token')
  if (!token) return null

  // Rebuild the socket when the signing-in user changes (login/logout of
  // another account) so the private channels reconnect with the fresh token.
  if (echo && echoUserId === userId) return echo
  if (echo) {
    echo.disconnect()
    echo = null
  }
  echoUserId = userId

  echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: ECHO_AUTH_URL,
    auth: {
      url: ECHO_AUTH_URL,
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    },
  })

  return echo
}

export function getEcho() {
  return echo
}

export function leaveEcho() {
  if (echo) {
    echo.disconnect()
    echo = null
  }
}
