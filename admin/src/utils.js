/**
 * Generates a unique content ID based on the current date and time.
 *
 * @returns String Unique content ID
 */
export function contentid() {
  const BASE64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_'
  const EPOCH = new Date('2025-01-01T00:00:00Z')
  const PRECISION = 30

  let steps = Math.floor((new Date() - EPOCH) / PRECISION)

  return Array.from({ length: 6 }, (_, i) =>
    BASE64[(steps >> (5 - i) * 6) & 63]
  ).join('')
}
