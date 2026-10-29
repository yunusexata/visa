import './bootstrap';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';

import Swal from 'sweetalert2';

// IMPORT ALPINE.JS (Wajib untuk Flux UI)
import Alpine from 'alpinejs';

import { UPLOAD_CONFIG } from './upload-config';
import { processUploadFile, validateFile, isPdf, isHeic, ready } from './upload-compress';

window.Swal = Swal;

// DAFTARKAN ALPINE KE WINDOW
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

// MULAI / JALANKAN ALPINE
Alpine.start();