import api from './http'

export const virtualApi = {
  cursos: {
    list: (params: Record<string, any>) => api.get('/virtual/cursos', { params }),
    create: (payload: { curso_id: number; description?: string }) => api.post('/virtual/cursos', payload),
    update: (id: number, payload: { curso_id?: number; description?: string }) => api.put(`/virtual/cursos/${id}`, payload),
    remove: (id: number) => api.delete(`/virtual/cursos/${id}`)
  },
  anuncios: {
    list: (params: Record<string, any>) => api.get('/virtual/anuncios', { params }),
    create: (payload: { curso_virtual_id: number; title: string; content: string }) =>
      api.post('/virtual/anuncios', payload),
    update: (id: number, payload: { curso_virtual_id?: number; title?: string; content?: string }) =>
      api.put(`/virtual/anuncios/${id}`, payload),
    remove: (id: number) => api.delete(`/virtual/anuncios/${id}`)
  },
  actividades: {
    list: (params: Record<string, any>) => api.get('/virtual/actividades', { params }),
    create: (payload: Record<string, any>) => api.post('/virtual/actividades', payload),
    update: (id: number, payload: Record<string, any>) => api.put(`/virtual/actividades/${id}`, payload),
    remove: (id: number) => api.delete(`/virtual/actividades/${id}`)
  },
  submissions: {
    list: (actividadId: number, params: Record<string, any>) =>
      api.get(`/virtual/actividades/${actividadId}/submissions`, { params }),
    my: (actividadId: number, params: Record<string, any> = {}) =>
      api.get(`/virtual/actividades/${actividadId}/submissions/me`, { params }),
    create: (actividadId: number, payload: Record<string, any>) =>
      api.post(`/virtual/actividades/${actividadId}/submissions`, payload),
    grade: (id: number, payload: Record<string, any>) => api.put(`/virtual/actividades/submissions/${id}`, payload)
  }
}
