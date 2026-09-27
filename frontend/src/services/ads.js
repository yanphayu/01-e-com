import { request } from './http'

export function getAds(placement) {
  const query = placement ? `?placement=${encodeURIComponent(placement)}` : ''
  return request(`/ads${query}`)
}
