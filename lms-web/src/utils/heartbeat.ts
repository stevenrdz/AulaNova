import { onBeforeUnmount, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { trackingApi } from '@/api/tracking'

export function useHeartbeat(courseId?: number | null) {
  const route = useRoute()
  let timer: number | undefined

  const resolveCourseId = () => {
    if (courseId !== undefined) return courseId
    const param = route.params.id
    if (typeof param === 'string') {
      const parsed = Number(param)
      return Number.isNaN(parsed) ? null : parsed
    }
    return null
  }

  const tick = async () => {
    if (document.visibilityState !== 'visible') return
    if (!document.hasFocus()) return
    try {
      await trackingApi.heartbeat({
        route: route.fullPath,
        course_id: resolveCourseId(),
        timestamp: Date.now()
      })
    } catch {
      // ignore
    }
  }

  onMounted(() => {
    tick()
    timer = window.setInterval(tick, 15000)
  })

  onBeforeUnmount(() => {
    if (timer) {
      window.clearInterval(timer)
    }
  })
}
