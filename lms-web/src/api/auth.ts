import api from './http'

export interface LoginPayload {
  email: string
  password: string
}

export const authApi = {
  login: (payload: LoginPayload) => api.post('/auth/login', payload),
  refresh: (refreshToken: string) => api.post('/auth/refresh', { refresh_token: refreshToken }),
  logout: (refreshToken: string) => api.post('/auth/logout', { refresh_token: refreshToken }),
  forgotPassword: (email: string) => api.post('/auth/forgot-password', { email }),
  resetPassword: (payload: { email: string; otp: string; new_password: string }) =>
    api.post('/auth/reset-password', payload)
}
