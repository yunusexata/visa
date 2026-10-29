import './bootstrap';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';

import Swal from 'sweetalert2';

// 1. IMPORT ALPINE
import Alpine from 'alpinejs';

import { UPLOAD_CONFIG } from './upload-config';
import { processUploadFile, validateFile, isPdf, isHeic, ready } from './upload-compress';

window.Swal = Swal;

// 2. DAFTARKAN ALPINE KE WINDOW SEBELUM PLUGIN/START
window.Alpine = Alpine;

window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

// File Upload Compression Helpers for Alpine.js (Native Canvas Engine)
window.UploadCompress = {
    config: UPLOAD_CONFIG,
    processUploadFile,
    validateFile,
    isPdf,
    isHeic,
    ready,
};

window.UploadCompressReady = true;
if (typeof window.dispatchEvent === 'function') {
    window.dispatchEvent(new CustomEvent('upload-compress:ready'));
}

// 3. START ALPINE (Pastikan dipanggil paling akhir setelah semua komponen/plugin terdaftar)
Alpine.start();