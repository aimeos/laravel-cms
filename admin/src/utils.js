/**
 * Generates a unique content ID based on the current date and time.
 *
 * @returns String Unique content ID
 */
const uid = (function () {
  const BASE64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_'
  const EPOCH = new Date('2025-01-01T00:00:00Z').getTime();

  let counter = 0

  return function() {
    counter = (counter + 1) % 16

    // IDs will repeat after ~70 years
    const value = (Math.floor((Date.now() - EPOCH) / 8) << 4) | counter

    return Array.from({ length: 7 }, (_, i) => {
      const index = (value >> (7 * (7 - i))) & 63
      return BASE64[i === 0 ? index % 52 : index]
    }).join('');
  }
})()

export { uid }
