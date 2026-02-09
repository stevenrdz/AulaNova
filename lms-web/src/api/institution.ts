import api from './http'

export const institutionApi = {
  get: () => api.get('/institution'),
  update: (payload: { logo_url?: string | null; primary_color?: string | null }) =>
    api.put('/institution', payload)
}
