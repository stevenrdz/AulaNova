# LMS Web (Vue 3 + DashUI)

SPA Vue 3 + Vite + TypeScript + Tailwind, reutilizando el layout y estilos de DashUI.

## Requisitos (DEV)
- Docker + Docker Compose

## Instalación rápida
1. Copia `.env.example` a `.env` y ajusta `VITE_API_URL`.
2. Desde la raíz del repo:

```bash
cd devops
docker compose -f docker-compose.dev.yml up --build
```

## Scripts
```bash
npm install
npm run dev
npm run build
```

## Integración DashUI
Los estilos base, layout (sidebar/topbar) y componentes UI (cards/tables/forms) se portan desde `dashui-tailwindcss/` a `src/styles/` y `src/components/`.
