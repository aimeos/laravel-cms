/**
 * Generates a unique content ID based on the current date and time.
 *
 * @returns String Unique content ID
 */
const contentid = (function () {
  const BASE64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_'
  const EPOCH = new Date('2025-01-01T00:00:00Z').getTime();

  let counter = 0

  return function() {
    counter = (counter + 1) % 16

    // IDs will repeat after 45.3 years
    const time = Math.floor(Date.now() - EPOCH) / 333
    const value = (time << 4) | counter;

    return Array.from({ length: 6 }, (_, i) =>
      BASE64[(value >> (6 * (5 - i))) & 63]
    ).join('');
  }
})()

export { contentid }
