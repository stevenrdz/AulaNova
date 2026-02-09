import { defineStore } from 'pinia'
import axios from 'axios'

export type UserRole = 'ROLE_ADMIN' | 'ROLE_TEACHER' | 'ROLE_STUDENT'

export interface UserInfo {
  id: number
  email: string
  first_name: string
  last_name: string
  roles: UserRole[]
}

interface AuthResponse {
  access_token: string
  refresh_token: string
  user: UserInfo
}

const API_URL = import.meta.env.VITE_API_URL as string

export const useAuthStore = defineStore('auth', {
  state: () => ({
    accessToken: '' as string,
    refreshToken: (localStorage.getItem('refresh_token') || '') as string,
    user: null as UserInfo | null,
    refreshPromise: null as Promise<void> | null
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.accessToken),
    hasRole: (state) => (role: UserRole) => state.user?.roles?.includes(role) ?? false
  },
  actions: {
    async login(email: string, password: string) {
      const response = await axios.post<AuthResponse>(`${API_URL}/auth/login`, { email, password })
      this.setSession(response.data)
    },
    async refresh() {
      if (!this.refreshToken) {
        throw new Error('No refresh token')
      }
      if (this.refreshPromise) {
        return this.refreshPromise
      }
      this.refreshPromise = (async () => {
        const response = await axios.post<AuthResponse>(`${API_URL}/auth/refresh`, {
          refresh_token: this.refreshToken
        })
        this.setSession(response.data)
      })()
      try {
        await this.refreshPromise
      } finally {
        this.refreshPromise = null
      }
    },
    async logout() {
      if (this.refreshToken) {
        await axios.post(`${API_URL}/auth/logout`, { refresh_token: this.refreshToken })
      }
      this.clearSession()
    },
    setSession(data: AuthResponse) {
      this.accessToken = data.access_token
      this.refreshToken = data.refresh_token
      this.user = data.user
      localStorage.setItem('refresh_token', data.refresh_token)
    },
    clearSession() {
      this.accessToken = ''
      this.refreshToken = ''
      this.user = null
      localStorage.removeItem('refresh_token')
    }
  }
})
