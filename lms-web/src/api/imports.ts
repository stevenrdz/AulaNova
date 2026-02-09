import api from './http'

export const importsApi = {
  listBatches: (params: Record<string, any>) => api.get('/imports/batches', { params }),
  getBatch: (id: number) => api.get(`/imports/batches/${id}`),
  importUsers: (payload: { file_id: number }) => api.post('/imports/users', payload)
}
