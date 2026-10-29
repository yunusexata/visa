# 📊 Laporan Analisis Composite Proxy Akses Dokumen Per-Client
**PT Sumber Rezeki Exata Indonesia**  
**Tanggal:** 2026-07-23  

---

## 1. Metodologi Proxy Composite yang Diterapkan

Sesuai arahan revisi metodologi:
1. **Sinyal Dihapus Total:** `client_magic_links.used_at` tidak digunakan sama sekali.
2. **Anchor Point (Waktu Notifikasi Terkirim):** Timestamp `finished_at` pada `send_email_logs` (`DocumentAvailableNotification`).
3. **Kriteria Confidence (Hierarki Tunggal tanpa Double-Count):**
   - **HIGH:** Terdapat record `upod_submissions` / `reviews` dari klien setelah timestamp notifikasi (`survey_review_at >= notification_sent_at`).
   - **MEDIUM:** Terdapat aktivitas sesi `sessions.last_activity` setelah timestamp notifikasi (dan tidak masuk kategori HIGH).
   - **NONE:** Notifikasi terkirim, tetapi tidak ditemukan sinyal HIGH maupun MEDIUM setelahnya. Label: *"notifikasi terkirim, tidak ada indikasi akses"*.

---

## 2. Ringkasan Evaluasi Klien

| Confidence Tier | Jumlah Klien | Deskripsi Ringkas |
| :--- | :---: | :--- |
| **HIGH** | **177** | Klien mengisi Survei Kepuasan / Ulasan Google di dashboard pasca notifikasi 3 dokumen lengkap terkirim. |
| **MEDIUM** | **0** | Klien memiliki sesi aktif di portal pasca notifikasi terkirim (di luar kelompok HIGH). |
| **NONE** | **461** | Notifikasi terkirim, tidak ada indikasi aktivitas/survei pasca notifikasi. |
| **TOTAL** | **638** | Total seluruh klien yang telah dikirimi notifikasi email ketersediaan dokumen lengkap. |

---

## 3. Sampel Data Evaluasi Per-Client (Top 20 Record)

| Client ID | Nama Klien | Notifikasi Terkirim (`finished_at`) | Survey/Review Submitted (setelah notif?) | Session Activity (setelah notif?) | Confidence |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 659 | BAMBANG BRAHMANA | 2026-07-04 11:06:20 | 2026-07-04 11:19:08 | - | **HIGH** |
| 619 | ALDIAN SHAFNUR | 2026-07-04 11:06:20 | 2026-07-04 12:41:02 | - | **HIGH** |
| 1223 | RAMA DIANSYAH | 2026-07-04 10:51:39 | 2026-07-04 15:06:19 | - | **HIGH** |
| 1076 | EKA HARIYADI | 2026-07-04 10:51:36 | 2026-07-04 22:54:42 | - | **HIGH** |
| 1302 | YUSUF MAHENDRA | 2026-07-04 10:51:34 | 2026-07-04 14:01:31 | - | **HIGH** |
| 1430 | I MADE FERDY KANA WINATA | 2026-07-04 10:51:32 | 2026-07-04 11:33:33 | - | **HIGH** |
| 1114 | HERI SETIAWAN NUGROHO | 2026-07-04 10:51:32 | 2026-07-04 18:40:57 | - | **HIGH** |
| 666 | BREMA SINUHAJI | 2026-07-04 10:51:31 | 2026-07-04 11:04:00 | - | **HIGH** |
| 1568 | TRIO ANGGARA | 2026-07-04 10:51:29 | 2026-07-07 14:26:15 | - | **HIGH** |
| 1068 | DICKY PERMADIYANTO | 2026-07-04 10:51:28 | 2026-07-04 13:08:51 | - | **HIGH** |
| 813 | MICHAEL HO HONG BOM | 2026-07-04 10:51:27 | 2026-07-04 18:11:52 | - | **HIGH** |
| 834 | MUHAMMAD JUMALUDDIN | 2026-07-04 10:51:26 | 2026-07-05 18:42:00 | - | **HIGH** |
| 997 | ADETIYA SEFRIYAN NUGROHO | 2026-07-04 10:51:25 | 2026-07-04 11:03:57 | - | **HIGH** |
| 1168 | MUHAMAD AJI BAYU PERMONO | 2026-07-04 10:51:22 | 2026-07-08 09:28:06 | - | **HIGH** |
| 1379 | DINANDA ARIZKI SAPUTRA | 2026-07-04 10:51:21 | 2026-07-04 10:54:04 | - | **HIGH** |
| 952 | SULAIKAN | 2026-07-04 11:06:21 | Tidak ada | Tidak ada | **NONE** |
| 1115 | I GEDE AGUS ARTAWAN | 2026-07-04 11:06:21 | Tidak ada | Tidak ada | **NONE** |
| 851 | NAUFA AL ZAHIRA | 2026-07-04 11:06:20 | Tidak ada | Tidak ada | **NONE** |
| 1502 | NININ SEPTIANI HERMAWATI | 2026-07-04 10:51:44 | Tidak ada | Tidak ada | **NONE** |
| 846 | MUSMUL YADI | 2026-07-04 10:51:44 | Tidak ada | Tidak ada | **NONE** |

*(Daftar lengkap 638 klien dapat di-query secara langsung dari database).*
