import imageCompression from 'browser-image-compression';
import heic2any from 'heic2any';
import { UPLOAD_CONFIG } from './upload-config';

/**
 * Cek apakah file merupakan PDF
 */
export function isPdf(file) {
  if (!file) return false;
  return file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
}

/**
 * Cek apakah file merupakan HEIC/HEIF
 */
export function isHeic(file) {
  if (!file) return false;
  const name = file.name.toLowerCase();
  return (
    file.type === 'image/heic' ||
    file.type === 'image/heif' ||
    name.endsWith('.heic') ||
    name.endsWith('.heif')
  );
}

/**
 * Cek apakah file valid sesuai konfigurasi MIME dan ekstensi
 */
export function validateFile(file) {
  if (!file) {
    return { valid: false, error: 'File tidak ditemukan.' };
  }

  const name = file.name.toLowerCase();
  const ext = name.split('.').pop();

  const isMimeAllowed = file.type && UPLOAD_CONFIG.allowedMimeTypes.includes(file.type.toLowerCase());
  const isExtAllowed = ext && UPLOAD_CONFIG.allowedExtensions.includes(ext);

  if (!isMimeAllowed && !isExtAllowed) {
    return {
      valid: false,
      error: `Format file ".${ext}" tidak didukung. Harap unggah format JPG, PNG, WEBP, HEIC, atau PDF.`,
    };
  }

  // Validasi ukuran PDF mentah
  if (isPdf(file)) {
    const maxPdfBytes = UPLOAD_CONFIG.pdfMaxSizeMB * 1024 * 1024;
    if (file.size > maxPdfBytes) {
      return {
        valid: false,
        error: `Ukuran file PDF (${(file.size / (1024 * 1024)).toFixed(1)}MB) melebihi batas maksimal ${UPLOAD_CONFIG.pdfMaxSizeMB}MB.`,
      };
    }
  } else {
    // Validasi ukuran mentah gambar sebelum kompresi
    const maxRawBytes = UPLOAD_CONFIG.rawImageMaxMB * 1024 * 1024;
    if (file.size > maxRawBytes) {
      return {
        valid: false,
        error: `Ukuran file mentah (${(file.size / (1024 * 1024)).toFixed(1)}MB) melebihi batas ${UPLOAD_CONFIG.rawImageMaxMB}MB.`,
      };
    }
  }

  return { valid: true };
}

/**
 * Memproses file: validasi -> convert HEIC jika perlu -> compress image -> return File siap upload
 * @param {File} rawFile
 * @param {Function} [onStatusChange] Callback status ({ stage, message, progress })
 * @returns {Promise<{ file: File, originalSizeKB: number, finalSizeKB: number, isCompressed: boolean }>}
 */
export async function processUploadFile(rawFile, onStatusChange = () => {}) {
  const originalSizeKB = Math.round(rawFile.size / 1024);

  // 1. Validasi
  onStatusChange({ stage: 'validating', message: 'Memvalidasi format file...', progress: 10 });
  const validation = validateFile(rawFile);
  if (!validation.valid) {
    throw new Error(validation.error);
  }

  // 2. Jika PDF, langsung kembalikan (tanpa kompresi)
  if (isPdf(rawFile)) {
    onStatusChange({ stage: 'ready', message: 'Dokumen PDF siap diunggah', progress: 100 });
    return {
      file: rawFile,
      originalSizeKB,
      finalSizeKB: originalSizeKB,
      isCompressed: false,
      isPdf: true,
    };
  }

  let fileToCompress = rawFile;

  // 3. Konversi HEIC/HEIF jika ada
  if (isHeic(rawFile)) {
    onStatusChange({ stage: 'converting', message: 'Mengonversi format HEIC/Apple...', progress: 30 });
    try {
      const conversionResult = await heic2any({
        blob: rawFile,
        toType: 'image/jpeg',
        quality: UPLOAD_CONFIG.heicConvertQuality,
      });

      const blobResult = Array.isArray(conversionResult) ? conversionResult[0] : conversionResult;
      const convertedName = rawFile.name.replace(/\.(heic|heif)$/i, '.jpg');
      fileToCompress = new File([blobResult], convertedName, { type: 'image/jpeg' });
    } catch (err) {
      console.error('[UploadCompress] Gagal konversi HEIC:', err);
      throw new Error('Gagal mengonversi foto format HEIC/Apple ke JPG. Harap simpan sebagai JPG terlebih dahulu.');
    }
  }

  // 4. Kompresi Gambar (browser-image-compression)
  onStatusChange({ stage: 'compressing', message: 'Mengompresi gambar di browser...', progress: 60 });

  try {
    const compressionOptions = {
      maxSizeMB: UPLOAD_CONFIG.maxSizeMB,
      maxWidthOrHeight: UPLOAD_CONFIG.maxWidthOrHeight,
      useWebWorker: UPLOAD_CONFIG.useWebWorker,
      fileType: 'image/jpeg',
      initialQuality: 0.85,
    };

    const compressedBlob = await imageCompression(fileToCompress, compressionOptions);
    const finalFile = new File([compressedBlob], fileToCompress.name, {
      type: compressedBlob.type || 'image/jpeg',
      lastModified: Date.now(),
    });

    const finalSizeKB = Math.round(finalFile.size / 1024);
    onStatusChange({ stage: 'ready', message: `Selesai kompresi (${finalSizeKB} KB)`, progress: 100 });

    return {
      file: finalFile,
      originalSizeKB,
      finalSizeKB,
      isCompressed: true,
      isPdf: false,
    };
  } catch (err) {
    console.warn('[UploadCompress] Kompresi gagal, fallback ke file asal:', err);
    // Jika kompresi gagal tapi file di bawah batas, fallback ke file asal
    return {
      file: fileToCompress,
      originalSizeKB,
      finalSizeKB: Math.round(fileToCompress.size / 1024),
      isCompressed: false,
      isPdf: false,
    };
  }
}
