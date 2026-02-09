import api from './http'

export const trackingApi = {
  heartbeat: (payload: { route: string; course_id?: number | null; timestamp: number }) =>
    api.post('/tracking/heartbeat', payload),
  summary: () => api.get('/tracking/summary')
}
