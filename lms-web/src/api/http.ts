import axios, { AxiosError } from 'axios'
import { useAuthStore } from '@/store/auth'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL as string
})

api.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.accessToken) {
    config.headers = config.headers || {}
    config.headers.Authorization = `Bearer ${auth.accessToken}`
  }
  return config
})

let isRefreshing = false
let pendingRequests: Array<(token?: string) => void> = []

const resolveQueue = (token?: string) => {
  pendingRequests.forEach((cb) => cb(token))
  pendingRequests = []
}

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const auth = useAuthStore()
    const status = error.response?.status
    const original = error.config as any

    if (status === 401 && auth.refreshToken && !original?._retry) {
      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          pendingRequests.push((token) => {
            if (!token) {
              reject(error)
              return
            }
            original.headers = original.headers || {}
            original.headers.Authorization = `Bearer ${token}`
            resolve(api(original))
          })
        })
      }

      original._retry = true
      isRefreshing = true

      try {
        await auth.refresh()
        const token = auth.accessToken
        resolveQueue(token)
        original.headers = original.headers || {}
        original.headers.Authorization = `Bearer ${token}`
        return api(original)
      } catch (err) {
        resolveQueue(undefined)
        auth.clearSession()
        return Promise.reject(err)
      } finally {
        isRefreshing = false
      }
    }

    return Promise.reject(error)
  }
)

export default api
