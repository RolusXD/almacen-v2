/**
 * Helper para formateo de fechas y manejo de Zona Horaria
 * Default: America/La_Paz (Bolivia UTC-4)
 */

export const DEFAULT_TIMEZONE = 'America/La_Paz';

let currentSystemTimezone = DEFAULT_TIMEZONE;

/**
 * Fija la zona horaria del proceso Node.js y los formateadores
 */
export function setSystemTimeZone(tz: string) {
  if (!tz || tz.trim() === '') {
    tz = DEFAULT_TIMEZONE;
  }
  currentSystemTimezone = tz.trim();
  process.env.TZ = currentSystemTimezone;
}

export function getSystemTimeZone(): string {
  return currentSystemTimezone;
}

/**
 * Formatea una fecha en formato legible "YYYY-MM-DD HH:mm:ss" en la zona horaria activa
 */
export function formatLocalDateTime(dateInput?: Date | string | null, tz?: string): string {
  if (!dateInput) return '-';
  const date = typeof dateInput === 'string' ? new Date(dateInput) : dateInput;
  if (isNaN(date.getTime())) return '-';

  const timeZone = tz || currentSystemTimezone;

  const formatter = new Intl.DateTimeFormat('es-BO', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  });

  // Reordenar a YYYY-MM-DD HH:mm:ss
  const parts = formatter.formatToParts(date);
  const partMap: Record<string, string> = {};
  for (const part of parts) {
    partMap[part.type] = part.value;
  }

  return `${partMap.year}-${partMap.month}-${partMap.day} ${partMap.hour}:${partMap.minute}:${partMap.second}`;
}

/**
 * Formatea una fecha en formato "YYYY-MM-DD"
 */
export function formatLocalDate(dateInput?: Date | string | null, tz?: string): string {
  if (!dateInput) return '-';
  const date = typeof dateInput === 'string' ? new Date(dateInput) : dateInput;
  if (isNaN(date.getTime())) return '-';

  const timeZone = tz || currentSystemTimezone;

  const formatter = new Intl.DateTimeFormat('es-BO', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });

  const parts = formatter.formatToParts(date);
  const partMap: Record<string, string> = {};
  for (const part of parts) {
    partMap[part.type] = part.value;
  }

  return `${partMap.year}-${partMap.month}-${partMap.day}`;
}
