<?php
// handler/command_help.php

$helpMsg = "🤖 *PANDUAN BOT REKAP JAJAN v2.0* 📊\n";
$helpMsg .= "Halo! Berikut adalah daftar perintah yang bisa kamu gunakan untuk mengelola keuangan grup secara otomatis.\n\n";

$helpMsg .= "📌 *1. PENDAFTARAN (Wajib Awal)*\n";
$helpMsg .= "👉 `/join` : Mendaftarkan diri kamu ke dalam sesi grup ini.\n\n";

$helpMsg .= "💰 *2. PENCATATAN TRANSAKSI*\n";
$helpMsg .= "👉 `/bayar [nominal] [keterangan] #[label] @username`\n";
$helpMsg .= "• _Contoh umum:_ `/bayar 15000 bakso` (Otomatis masuk sesi #umum)\n";
$helpMsg .= "• _Contoh spesifik:_ `/bayar 260000 paragon #mei @princess` (Mencatat pengeluaran Princess untuk proyek #mei)\n\n";

$helpMsg .= "🔄 *3. MANAJEMEN & KOREKSI (Gunakan sistem Reply)*\n";
$helpMsg .= "👉 `/edit [nominal] [keterangan] #[label] @username`\n";
$helpMsg .= "• _Cara pakai:_ Balas (reply) pesan konfirmasi transaksi dari bot, lalu ketik perintah edit yang baru.\n";
$helpMsg .= "👉 `/hapus`\n";
$helpMsg .= "• _Cara pakai:_ Balas (reply) pesan konfirmasi bot yang ingin dihapus (Maksimal 1 jam setelah dicatat).\n\n";

$helpMsg .= "💸 *4. CICILAN / PELUNASAN SEBAGIAN*\n";
$helpMsg .= "👉 `/cicil [nominal] @username_tujuan #[label]`\n";
$helpMsg .= "• _Contoh:_ `/cicil 50000 @princess #mei` (Mencatat kalau kamu sudah bayar hutang Rp 50.000 ke Princess)\n\n";

$helpMsg .= "📊 *5. MONITORING & LAPORAN*\n";
$helpMsg .= "👉 `/rekap #[label]` : Melihat total pengeluaran, bagi rata, dan sisa hutang real-time di sesi aktif.\n";
$helpMsg .= "👉 `/history #[label]` : Melihat semua riwayat daftar transaksi yang berstatus aktif.\n";
$helpMsg .= "👉 `/selesai #[label]` : Menutup sesi aktif (Gunakan jika semua hutang di label tersebut sudah lunas).\n\n";

$helpMsg .= "💡 _Tips: Penggunaan tanda pagar (#[label]) bersifat opsional. Jika dikosongkan, bot otomatis mengarahkannya ke label_ `#umum`.";

sendMessage($chatId, $helpMsg);