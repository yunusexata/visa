import { UPLOAD_CONFIG } from './upload-config';

/**
 * Cek apakah file merupakan PDF
 */
export function isPdf(file) {
  if (!file) return false;
  return file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
}

/**
 * Cek apakah file merupakan HEIC/HEIF (Apple format)
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
 * Validasi awal format berkas dan batasan ukuran
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
        error: `Ukuran file mentah (${(file.size / (1024 * 1024)).toFixed(1)}MB) melebihi batas maksimal ${UPLOAD_CONFIG.rawImageMaxMB}MB.`,
      };
    }
  }

  return { valid: true };
}

/**
 * Memuat gambar ke elemen Image atau ImageBitmap secara native
 */
async function loadImageSource(file) {
  // Coba native createImageBitmap terlebih dahulu (hardware-accelerated, async)
  if (typeof window.createImageBitmap === 'function' && !isHeic(file)) {
    try {
      const bitmap = await window.createImageBitmap(file);
      return {
        source: bitmap,
        width: bitmap.width,
        height: bitmap.height,
        cleanup: () => bitmap.close && bitmap.close(),
      };
    } catch (e) {
      // Fallback ke Image standard
    }
  }

  // Jika HEIC dan browser bukan Safari (yang mendukung HEIC native), gunakan heic2any
  let targetBlob = file;
  if (isHeic(file)) {
    try {
      const { default: heic2any } = await import('heic2any');
      const conversion = await heic2any({
        blob: file,
        toType: 'image/jpeg',
        quality: 0.88,
      });
      targetBlob = Array.isArray(conversion) ? conversion[0] : conversion;
    } catch (heicErr) {
      console.warn('[UploadCompress] heic2any konversi gagal atau dilewati:', heicErr);
    }
  }

  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(targetBlob);
    const img = new Image();

    img.onload = () => {
      resolve({
        source: img,
        width: img.naturalWidth || img.width,
        height: img.naturalHeight || img.height,
        cleanup: () => {
          try { URL.revokeObjectURL(url); } catch (e) {}
        },
      });
    };

    img.onerror = () => {
      try { URL.revokeObjectURL(url); } catch (e) {}
      reject(new Error('Gagal membaca data gambar dari berkas. Format mungkin rusak atau tidak didukung.'));
    };

    img.src = url;
  });
}

/**
 * Menghitung dimensi skala target dengan mempertahankan aspect ratio
 */
function calculateTargetDimensions(width, height, maxDimension) {
  let targetWidth = width;
  let targetHeight = height;

  if (targetWidth > maxDimension || targetHeight > maxDimension) {
    if (targetWidth > targetHeight) {
      targetHeight = Math.round((targetHeight * maxDimension) / targetWidth);
      targetWidth = maxDimension;
    } else {
      targetWidth = Math.round((targetWidth * maxDimension) / targetHeight);
      targetHeight = maxDimension;
    }
  }

  return { targetWidth, targetHeight };
}

/**
 * Mengubah canvas menjadi Blob JPEG dengan kualitas tertentu
 */
function canvasToJpegBlob(canvas, quality) {
  return new Promise((resolve) => {
    canvas.toBlob(
      (blob) => resolve(blob),
      UPLOAD_CONFIG.outputMimeType,
      quality
    );
  });
}

/**
 * Kompresi gambar Native Canvas dengan Adaptive Quality Tuning.
 * Menjamin hasil selalu <= targetSizeKB (500KB) dengan kualitas visual optimal.
 */
async function compressCanvasNative(imageObj, targetSizeKB, maxDimension, onStatusChange) {
  const targetBytes = targetSizeKB * 1024;
  let { targetWidth, targetHeight } = calculateTargetDimensions(
    imageObj.width,
    imageObj.height,
    maxDimension
  );

  const canvas = document.createElement('canvas');
  let ctx = canvas.getContext('2d');

  canvas.width = targetWidth;
  canvas.height = targetHeight;

  // Background putih jika gambar aslinya transparan (PNG)
  ctx.fillStyle = '#FFFFFF';
  ctx.fillRect(0, 0, targetWidth, targetHeight);
  ctx.drawImage(imageObj.source, 0, 0, targetWidth, targetHeight);

  // Kualitas bertahap untuk adaptasi ke target <= 500KB
  const qualitySteps = [
    UPLOAD_CONFIG.initialQuality, // 0.82
    0.72,
    0.60,
    UPLOAD_CONFIG.minQuality,     // 0.50
  ];

  let bestBlob = null;

  for (let i = 0; i < qualitySteps.length; i++) {
    const q = qualitySteps[i];
    onStatusChange({
      stage: 'compressing',
      message: `Mengoptimasi gambar (${Math.round(q * 100)}%)...`,
      progress: 40 + i * 15,
    });

    const blob = await canvasToJpegBlob(canvas, q);
    if (!blob) continue;

    bestBlob = blob;

    // Jika ukuran sudah di bawah target, hentikan iterasi
    if (blob.size <= targetBytes) {
      break;
    }
  }

  // Jika setelah kualitas minimal masih > targetBytes (misal foto sangat kompleks),
  // lakukan 1x resize resolusi 75%
  if (bestBlob && bestBlob.size > targetBytes) {
    onStatusChange({
      stage: 'compressing',
      message: 'Menyesuaikan resolusi untuk memenuhi target...',
      progress: 90,
    });

    const resizedWidth = Math.round(targetWidth * 0.75);
    const resizedHeight = Math.round(targetHeight * 0.75);

    canvas.width = resizedWidth;
    canvas.height = resizedHeight;

    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, resizedWidth, resizedHeight);
    ctx.drawImage(imageObj.source, 0, 0, resizedWidth, resizedHeight);

    const finalBlob = await canvasToJpegBlob(canvas, 0.65);
    if (finalBlob) {
      bestBlob = finalBlob;
    }
  }

  // Bersihkan canvas dari memori
  canvas.width = 0;
  canvas.height = 0;

  return bestBlob;
}

/**
 * Normalisasi nama file ke ekstensi .jpg
 */
function normalizeOutputFilename(originalName) {
  const dotIndex = originalName.lastIndexOf('.');
  const baseName = dotIndex !== -1 ? originalName.substring(0, dotIndex) : originalName;
  return `${baseName}.${UPLOAD_CONFIG.outputExtension}`;
}

/**
 * Memproses file secara menyeluruh:
 * Validasi -> Deteksi PDF/Image -> Kompresi Native Canvas -> Output File murni siap upload
 *
 * @param {File} rawFile
 * @param {Function} [onStatusChange] Callback status ({ stage, message, progress })
 * @returns {Promise<{ file: File, originalSizeKB: number, finalSizeKB: number, isCompressed: boolean, isPdf: boolean }>}
 */
export async function processUploadFile(rawFile, onStatusChange = () => {}) {
  const originalSizeKB = Math.round(rawFile.size / 1024);

  // 1. Validasi
  onStatusChange({ stage: 'validating', message: 'Memvalidasi format berkas...', progress: 10 });
  const validation = validateFile(rawFile);
  if (!validation.valid) {
    throw new Error(validation.error);
  }

  // 2. Jika PDF, langsung kembalikan (tanpa kompresi lossy)
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

  // 3. Muat gambar ke Image Source
  console.log('[UploadCompress] Memulai proses kompresi berkas:', rawFile.name, `(${originalSizeKB} KB)`);
  onStatusChange({ stage: 'compressing', message: 'Membaca data gambar...', progress: 30 });
  let imageSourceObj = null;

  try {
    imageSourceObj = await loadImageSource(rawFile);
  } catch (loadErr) {
    throw new Error(`Gagal memuat gambar "${rawFile.name}": ${loadErr.message}`);
  }

  // 4. Kompresi gambar via Native Canvas
  try {
    const compressedBlob = await compressCanvasNative(
      imageSourceObj,
      UPLOAD_CONFIG.targetSizeKB,
      UPLOAD_CONFIG.maxWidthOrHeight,
      onStatusChange
    );

    if (!compressedBlob) {
      throw new Error('Gagal menghasilkan berkas kompresi gambar.');
    }

    const outputName = normalizeOutputFilename(rawFile.name);
    const finalFile = new File([compressedBlob], outputName, {
      type: UPLOAD_CONFIG.outputMimeType,
      lastModified: Date.now(),
    });

    const finalSizeKB = Math.round(finalFile.size / 1024);
    const isCompressed = finalSizeKB < originalSizeKB;
    const savings = isCompressed ? Math.round((1 - finalSizeKB / originalSizeKB) * 100) : 0;

    console.log('[UploadCompress] Berhasil dikompresi menjadi:', finalFile.name, `(${finalSizeKB} KB)`, isCompressed ? `(hemat ${savings}%)` : '(ukuran sudah optimal)');

    onStatusChange({
      stage: 'ready',
      message: isCompressed
        ? `Selesai dikompresi (${finalSizeKB} KB)`
        : `Ukuran optimal (${finalSizeKB} KB)`,
      progress: 100,
    });

    return {
      file: finalFile,
      originalSizeKB,
      finalSizeKB,
      isCompressed,
      isPdf: false,
    };
  } finally {
    if (imageSourceObj && typeof imageSourceObj.cleanup === 'function') {
      imageSourceObj.cleanup();
    }
  }
}

/**
 * Menunggu status window.UploadCompress siap
 */
export function ready() {
  if (typeof window !== 'undefined' && window.UploadCompressReady) {
    return Promise.resolve();
  }
  return new Promise((resolve) => {
    window.addEventListener('upload-compress:ready', () => resolve(), { once: true });
    // Timeout fallback agar tidak pernah menggantung selamanya
    setTimeout(resolve, 3000);
  });
}
