import api from './http'

export const filesApi = {
  presign: (payload: { filename: string; mime_type: string; size: number }) =>
    api.post('/files/presign', payload),
  complete: (payload: {
    key: string
    bucket: string
    original_name: string
    mime_type: string
    size: number
  }) => api.post('/files/complete', payload),
  download: (id: number, params?: { disposition?: 'attachment' | 'inline'; filename?: string }) =>
    api.get(`/files/${id}/download`, { params })
}
