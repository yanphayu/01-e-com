<template>
  <Teleport to="body">
    <div v-if="modelValue" class="map-overlay" @click.self="close">
      <div class="map-modal">
        <header class="map-head">
          <h3>{{ t('auth.selectLocationOnMap') }}</h3>
          <button class="map-close" type="button" aria-label="Close" @click="close">×</button>
        </header>

        <div ref="mapContainer" class="map-container"></div>

        <p v-if="geocoding" class="map-status">{{ t('auth.gettingAddress') }}…</p>
        <p v-else-if="selectedAddress" class="map-status map-status-ok">{{ selectedAddress }}</p>
        <p v-else class="map-status">{{ t('auth.clickOnMap') }}</p>

        <footer class="map-foot">
          <BaseButton variant="ghost" @click="close">{{ t('auth.cancelEdit') }}</BaseButton>
          <BaseButton variant="primary" :disabled="!selectedLat" @click="confirm">{{ t('auth.confirm') }}</BaseButton>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onBeforeUnmount } from 'vue'
import { t, getLocale } from '../i18n'
import BaseButton from './BaseButton.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  latitude: { type: Number, default: null },
  longitude: { type: Number, default: null },
})

const emit = defineEmits(['update:modelValue', 'update:latitude', 'update:longitude', 'update:address', 'confirm'])

const mapContainer = ref(null)
const selectedLat = ref(props.latitude)
const selectedLng = ref(props.longitude)
const selectedAddress = ref('')
const selectedProvince = ref('')
const selectedKhan = ref('')
const selectedSangkat = ref('')
const geocoding = ref(false)

let map = null
let marker = null

const DEFAULT_LAT = 11.5564
const DEFAULT_LNG = 104.9282

function initMap() {
  if (map || !mapContainer.value) return

  import('leaflet').then((L) => {
    const centerLat = props.latitude ?? DEFAULT_LAT
    const centerLng = props.longitude ?? DEFAULT_LNG

    map = L.map(mapContainer.value, {
      attributionControl: false,
    }).setView([centerLat, centerLng], 13)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
    }).addTo(map)

    if (props.latitude && props.longitude) {
      marker = makeMarker(L, props.latitude, props.longitude)
    }

    map.on('click', onMapClick)
  })
}

function makeMarker(L, lat, lng) {
  const m = L.marker([lat, lng], {
    icon: L.divIcon({
      className: 'loc-pin',
      html: '<svg viewBox="0 0 24 24" width="32" height="32" style="display:block"><path d="M12 2a8 8 0 0 0-8 8c0 5.4 8 12 8 12s8-6.6 8-12a8 8 0 0 0-8-8z" fill="var(--accent, #2f9e6b)" stroke="#fff" stroke-width="2"/><circle cx="12" cy="10" r="3" fill="#fff"/></svg>',
      iconSize: [32, 32],
      iconAnchor: [16, 30],
    }),
    draggable: true,
  }).addTo(map)
  m.on('dragend', onMarkerDrag)
  return m
}

function onMapClick(e) {
  const { lat, lng } = e.latlng
  selectedLat.value = lat
  selectedLng.value = lng

  import('leaflet').then((L) => {
    if (marker) {
      marker.setLatLng([lat, lng])
    } else {
      marker = makeMarker(L, lat, lng)
    }
  })

  reverseGeocode(lat, lng)
}

function onMarkerDrag(e) {
  const { lat, lng } = e.target.getLatLng()
  selectedLat.value = lat
  selectedLng.value = lng
  reverseGeocode(lat, lng)
}

async function reverseGeocode(lat, lng) {
  geocoding.value = true
  selectedAddress.value = ''
  selectedProvince.value = ''
  selectedKhan.value = ''
  selectedSangkat.value = ''
  try {
    const lang = getLocale() === 'kh' ? 'km' : getLocale()
    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=${lang}`
    )
    const data = await res.json()
    const addr = data.address || {}
    selectedAddress.value = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`
    selectedProvince.value = addr.state || addr.region || ''
    const rawKhan = addr.town || addr.city || addr.county || addr.suburb || ''
    selectedKhan.value = rawKhan.replace(/^Khan\s+/i, '').trim()
    const rawSangkat = addr.village || addr.suburb || addr.neighbourhood || ''
    selectedSangkat.value = rawSangkat.replace(/^Sangkat\s+/i, '').trim()
  } catch {
    selectedAddress.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`
  } finally {
    geocoding.value = false
  }
}

function confirm() {
  const payload = {
    latitude: selectedLat.value,
    longitude: selectedLng.value,
    address: selectedAddress.value,
    province: selectedProvince.value,
    khan: selectedKhan.value,
    sangkat: selectedSangkat.value,
  }
  emit('update:latitude', payload.latitude)
  emit('update:longitude', payload.longitude)
  emit('update:address', payload.address)
  emit('confirm', payload)
  close()
}

function close() {
  emit('update:modelValue', false)
  destroyMap()
}

function destroyMap() {
  if (map) {
    map.remove()
    map = null
    marker = null
  }
}

watch(() => props.modelValue, (open) => {
  if (open) {
    selectedLat.value = props.latitude
    selectedLng.value = props.longitude
    selectedAddress.value = ''
    selectedProvince.value = ''
    selectedKhan.value = ''
    selectedSangkat.value = ''
    nextTick(() => initMap())
  } else {
    destroyMap()
  }
})

onBeforeUnmount(() => {
  destroyMap()
})
</script>

<style>
.loc-pin {
  background: none;
  border: none;
  filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.3));
}

.loc-pin svg {
  pointer-events: none;
}
</style>

<style scoped>
.map-overlay {
  position: fixed;
  inset: 0;
  z-index: 200;
  display: grid;
  place-items: center;
  padding: 1.25rem;
  background: rgba(28, 27, 25, 0.45);
  backdrop-filter: blur(2px);
}

.map-modal {
  width: 100%;
  max-width: 720px;
  max-height: 90vh;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.map-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}

.map-head h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
}

.map-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: var(--text-muted);
  cursor: pointer;
}
.map-close:hover {
  color: var(--text);
}

.map-container {
  width: 100%;
  height: 400px;
  flex-shrink: 0;
}

.map-status {
  margin: 0;
  padding: 0.6rem 1.25rem;
  font-size: 0.82rem;
  color: var(--text-muted);
  border-top: 1px solid var(--border);
  background: var(--surface-2);
}

.map-status-ok {
  color: var(--text);
}

.map-foot {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  padding: 0.85rem 1.25rem;
  border-top: 1px solid var(--border);
  flex-shrink: 0;
}
</style>
