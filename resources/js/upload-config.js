export const UPLOAD_CONFIG = {
  // Target ukuran setelah kompresi untuk gambar
  targetSizeKB: 500,
  maxSizeMB: 0.5,             // 500KB = 0.5MB

  // Batas resolusi maksimal
  maxWidthOrHeight: 1920,
  useWebWorker: true,

  // MIME types yang diizinkan
  allowedMimeTypes: [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/webp',
    'image/heic',
    'image/heif',
    'application/pdf',
  ],

  // Ekstensi fallback jika mime tidak terdeteksi oleh browser
  allowedExtensions: ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'pdf'],

  // Konversi HEIC/HEIF ke JPEG
  heicConvertQuality: 0.85,

  // PDF tidak dikompresi, batas ukuran mentah 2MB
  pdfMaxSizeMB: 2,

  // Batas ukuran awal file mentah (sebelum kompresi)
  rawImageMaxMB: 20,
};
