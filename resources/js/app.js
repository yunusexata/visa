import './bootstrap';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';

import Swal from 'sweetalert2';
import { UPLOAD_CONFIG } from './upload-config';
import { processUploadFile, validateFile, isPdf, isHeic } from './upload-compress';

window.Swal = Swal;
// window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

// File Upload Compression Helpers for Alpine.js
window.UploadCompress = {
    config: UPLOAD_CONFIG,
    processUploadFile,
    validateFile,
    isPdf,
    isHeic,
};

// Alpine.start();
