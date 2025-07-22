import * as lame from '@breezystack/lamejs';



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
    counter = (counter + 1) % 8

    // IDs will repeat after ~70 years
    const value = (((Date.now() - EPOCH) / 256) << 3) | counter

    return Array.from({ length: 6 }, (_, i) => {
      const index = (value >> (6 * (5 - i))) & 63
      return BASE64[i === 0 ? index % 52 : index]
    }).join('');
  }
})()

export { uid }



/**
 * Converts a Float32Array (-1 to 1) to Int16Array (-32768 to 32767).
 *
 * @param {Float32Array} float32 - PCM float samples
 * @returns {Int16Array} - PCM 16-bit samples
 */
function convert(float32) {
  const int16 = new Int16Array(float32.length);

  for(let i = 0; i < float32.length; i++) {
    const s = Math.max(-1, Math.min(1, float32[i]));
    int16[i] = s < 0 ? s * 0x8000 : s * 0x7FFF;
  }

  return int16;
}


/**
 * Mixes any AudioBuffer (mono or stereo) into a mono Float32Array.
 *
 * @param {AudioBuffer} buffer - Input AudioBuffer
 * @returns {Float32Array} - Mono PCM samples
 */
function toMono(buffer) {
  const { numberOfChannels, length } = buffer;

  if(numberOfChannels === 1) {
    return buffer.getChannelData(0); // already mono
  }

  const left = buffer.getChannelData(0);
  const right = buffer.getChannelData(1);
  const mono = new Float32Array(length);

  for(let i = 0; i < length; i++) {
    mono[i] = (left[i] + right[i]) / 2;
  }

  return mono;
}


/**
 * Encodes a mono Float32Array to MP3 using lamejs.
 *
 * @param {Float32Array} pcm - Mono PCM float samples
 * @param {number} sampleRate - Audio sample rate
 * @param {number} bitrate - Target bitrate in kbps
 * @returns {Blob} - MP3 blob
 */
function encode(pcm, sampleRate, bitrate) {
  const encoder = new lame.Mp3Encoder(1, sampleRate, bitrate);
  const samples = convert(pcm);
  const blockSize = 1152;
  const mp3 = [];

  for (let i = 0; i < samples.length; i += blockSize) {
    const chunk = samples.subarray(i, i + blockSize);
    const buffer = encoder.encodeBuffer(chunk);

    if(buffer.length > 0) mp3.push(new Uint8Array(buffer));
  }

  const flush = encoder.flush();

  if(flush.length > 0) mp3.push(new Uint8Array(flush));

  return new Blob(mp3, { type: 'audio/mpeg' });
}


/**
 * Extracts the audio from an URL and converts it to a MP3 file (mono).
 *
 * @param {string} url - URL of the media file.
 * @returns {Blob} - MP3 blob
 */
export async function url2audio(url) {
  const context = new AudioContext();

  const response = await fetch(url);
  const arrayBuffer = await response.arrayBuffer();
  const audioBuffer = await context.decodeAudioData(arrayBuffer);

  return encode(toMono(audioBuffer), audioBuffer.sampleRate, 64);
}
