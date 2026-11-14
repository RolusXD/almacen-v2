# ==========================================================
# DOCKERFILE: Sistema de Almacén TOI v4.0 (Node.js & TypeScript)
# Multi-stage build optimizado para Coolify
# ==========================================================

# Etapa 1: Compilación (Builder)
FROM node:22-alpine AS builder

WORKDIR /app

# Copiar manifiestos
COPY package*.json tsconfig.json ./

# Instalar todas las dependencias para compilar
RUN npm ci

# Copiar código fuente
COPY src/ ./src/

# Compilar TypeScript y copiar vistas/estáticos
RUN npm run build

# Etapa 2: Entorno de Producción Ligero (Runner)
FROM node:22-alpine AS runner

WORKDIR /app

ENV NODE_ENV=production
ENV PORT=3000

# Instalar únicamente dependencias de producción
COPY package*.json ./
RUN npm ci --omit=dev && npm cache clean --force

# Copiar artefactos compilados desde el builder
COPY --from=builder /app/dist ./dist

# Crear usuario sin privilegios por seguridad
USER node

# Exponer el puerto de la aplicación
EXPOSE 3000

# Healthcheck para Coolify
HEALTHCHECK --interval=20s --timeout=5s --start-period=10s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost:3000/health || exit 1

# Comando de arranque
CMD ["node", "dist/server.js"]
