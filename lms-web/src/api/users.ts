import api from './http'

export interface UserPayload {
  email: string
  password?: string
  first_name: string
  last_name: string
  role?: string
}

export const usersApi = {
  list: (role: string) => api.get('/users', { params: { role } }),
  create: (payload: UserPayload) => api.post('/users', payload),
  update: (id: number, payload: Partial<UserPayload>) => api.put(`/users/${id}`, payload),
  remove: (id: number) => api.delete(`/users/${id}`)
}
