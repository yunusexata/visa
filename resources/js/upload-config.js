export const UPLOAD_CONFIG = {
  // Target ukuran akhir setelah kompresi untuk gambar (dalam KB)
  targetSizeKB: 500,

  // Batas resolusi dimensi maksimal (panjang atau lebar)
  maxWidthOrHeight: 1920,

  // Kualitas kompresi awal dan batas minimal
  initialQuality: 0.82,
  minQuality: 0.50,

  // Format gambar output selalu JPEG standar yang optimal
  outputMimeType: 'image/jpeg',
  outputExtension: 'jpg',

  // PDF tidak dikompresi (batas validasi mentah 2MB)
  pdfMaxSizeMB: 2,

  // Batas toleransi file mentah awal sebelum diproses (5MB)
  rawImageMaxMB: 5,

  // MIME types yang diizinkan untuk diunggah
  allowedMimeTypes: [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/webp',
    'image/heic',
    'image/heif',
    'application/pdf',
  ],

  // Ekstensi fallback jika mime-type tidak terdeteksi oleh browser
  allowedExtensions: ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'pdf'],
};
