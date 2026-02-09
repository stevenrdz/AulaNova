import api from './http'

export const structureApi = {
  sedeJornadas: {
    list: (params: Record<string, any>) => api.get('/structure/sede-jornadas', { params }),
    create: (payload: { name: string; is_active?: boolean }) => api.post('/structure/sede-jornadas', payload),
    update: (id: number, payload: { name?: string; is_active?: boolean }) => api.put(`/structure/sede-jornadas/${id}`, payload),
    remove: (id: number) => api.delete(`/structure/sede-jornadas/${id}`)
  },
  niveles: {
    list: (params: Record<string, any>) => api.get('/structure/niveles', { params }),
    create: (payload: { name: string; is_active?: boolean }) => api.post('/structure/niveles', payload),
    update: (id: number, payload: { name?: string; is_active?: boolean }) => api.put(`/structure/niveles/${id}`, payload),
    remove: (id: number) => api.delete(`/structure/niveles/${id}`)
  },
  periodos: {
    list: (params: Record<string, any>) => api.get('/structure/periodos', { params }),
    create: (payload: { name: string; start_date?: string; end_date?: string }) => api.post('/structure/periodos', payload),
    update: (id: number, payload: { name?: string; start_date?: string; end_date?: string }) => api.put(`/structure/periodos/${id}`, payload),
    remove: (id: number) => api.delete(`/structure/periodos/${id}`)
  },
  asignaturas: {
    list: (params: Record<string, any>) => api.get('/structure/asignaturas', { params }),
    create: (payload: { name: string; is_active?: boolean }) => api.post('/structure/asignaturas', payload),
    update: (id: number, payload: { name?: string; is_active?: boolean }) => api.put(`/structure/asignaturas/${id}`, payload),
    remove: (id: number) => api.delete(`/structure/asignaturas/${id}`)
  },
  carreras: {
    list: (params: Record<string, any>) => api.get('/structure/carreras', { params }),
    create: (payload: { name: string; is_active?: boolean }) => api.post('/structure/carreras', payload),
    update: (id: number, payload: { name?: string; is_active?: boolean }) => api.put(`/structure/carreras/${id}`, payload),
    remove: (id: number) => api.delete(`/structure/carreras/${id}`)
  },
  cursos: {
    list: (params: Record<string, any>) => api.get('/structure/cursos', { params }),
    create: (payload: Record<string, any>) => api.post('/structure/cursos', payload),
    update: (id: number, payload: Record<string, any>) => api.put(`/structure/cursos/${id}`, payload),
    remove: (id: number) => api.delete(`/structure/cursos/${id}`)
  }
}
