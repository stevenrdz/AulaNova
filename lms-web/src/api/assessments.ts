import api from './http'

export const assessmentsApi = {
  quizzes: {
    list: (params: Record<string, any>) => api.get('/assessments/quizzes', { params }),
    create: (payload: Record<string, any>) => api.post('/assessments/quizzes', payload),
    update: (id: number, payload: Record<string, any>) => api.put(`/assessments/quizzes/${id}`, payload),
    remove: (id: number) => api.delete(`/assessments/quizzes/${id}`)
  },
  questions: {
    list: (params: Record<string, any>) => api.get('/assessments/questions', { params }),
    create: (payload: Record<string, any>) => api.post('/assessments/questions', payload),
    update: (id: number, payload: Record<string, any>) => api.put(`/assessments/questions/${id}`, payload),
    remove: (id: number) => api.delete(`/assessments/questions/${id}`)
  },
  attempts: {
    list: (params: Record<string, any>) => api.get('/assessments/attempts', { params }),
    create: (payload: { quiz_id: number }) => api.post('/assessments/attempts', payload),
    finish: (id: number) => api.post(`/assessments/attempts/${id}/finish`),
    remove: (id: number) => api.delete(`/assessments/attempts/${id}`)
  },
  answers: {
    list: (params: Record<string, any>) => api.get('/assessments/answers', { params }),
    create: (payload: Record<string, any>) => api.post('/assessments/answers', payload),
    update: (id: number, payload: Record<string, any>) => api.put(`/assessments/answers/${id}`, payload),
    remove: (id: number) => api.delete(`/assessments/answers/${id}`)
  }
}
