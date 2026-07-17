<?php
// handler/command_help.php

$helpMsg = "🤖 *PANDUAN BOT REKAP JAJAN v2.0* 📊\n"; //[cite: 6]
$helpMsg .= "Halo! Berikut adalah daftar perintah yang bisa kamu gunakan untuk mengelola keuangan grup secara otomatis.\n\n"; //[cite: 6]

$helpMsg .= "📌 *1. PENDAFTARAN (Wajib Awal)*\n"; //[cite: 6]
$helpMsg .= "👉 `/join` : Mendaftarkan diri kamu ke dalam sesi grup ini.\n\n"; //[cite: 6]

$helpMsg .= "💰 *2. PENCATATAN TRANSAKSI KELOMPOK (Bagi Rata)*\n";
$helpMsg .= "👉 `/bayar [nominal] [keterangan] #[label] @username`\n"; //[cite: 6]
$helpMsg .= "• _Contoh umum:_ `/bayar 15000 bakso` (Otomatis masuk sesi #umum)\n"; //[cite: 6]
$helpMsg .= "• _Contoh spesifik:_ `/bayar 260000 paragon #mei @princess` (Mencatat pengeluaran Princess untuk proyek #mei)\n\n"; //[cite: 6]

$helpMsg .= "📌 *3. HUTANG PIUTANG PRIBADI (Tidak Bagi Rata)*\n";
$helpMsg .= "👉 `/pinjam [nominal] [keterangan] @username_target #[label]`\n";
$helpMsg .= "• _Kegunaan:_ Dipakai saat **kamu meminjamkan uang** ke orang lain (Beban 100% ke target).\n";
$helpMsg .= "• _Contoh:_ `/pinjam 200000 beli bensin @rara` (Kamu pinjamin Rara 200rb)\n";
$helpMsg .= "👉 `/utang [nominal] [keterangan] @username_target #[label]`\n";
$helpMsg .= "• _Kegunaan:_ Dipakai saat **kamu meminjam uang** dari orang lain (Beban 100% ke dirimu).\n";
$helpMsg .= "• _Contoh:_ `/utang 200000 beli bensin @tommy` (Rara mencatat dia utang ke Tommy 200rb)\n";
$helpMsg .= "• _Tips:_ Bisa juga dipakai dengan sistem *Reply* pesan target + langsung ketik perintahnya tanpa mention.\n\n";

$helpMsg .= "🔄 *4. MANAJEMEN & KOREKSI (Gunakan sistem Reply)*\n"; //[cite: 6]
$helpMsg .= "👉 `/edit [nominal] [keterangan] #[label] @username`\n"; //[cite: 6]
$helpMsg .= "• _Cara pakai:_ Balas (reply) pesan konfirmasi transaksi dari bot, lalu ketik perintah edit yang baru.\n"; //[cite: 6]
$helpMsg .= "👉 `/hapus`\n"; //[cite: 6]
$helpMsg .= "• _Cara pakai:_ Balas (reply) pesan konfirmasi bot yang ingin dihapus (Bisa untuk /bayar, /pinjam, atau /utang).\n\n";

$helpMsg .= "💸 *5. CICILAN / PELUNASAN SEBAGIAN*\n"; //[cite: 6]
$helpMsg .= "👉 `/cicil [nominal] @username_tujuan #[label]`\n"; //[cite: 6]
$helpMsg .= "• _Contoh:_ `/cicil 50000 @princess #mei` (Mencatat kalau kamu sudah bayar hutang Rp 50.000 ke Princess)\n\n"; //[cite: 6]

$helpMsg .= "📊 *6. MONITORING & LAPORAN*\n"; //[cite: 6]
$helpMsg .= "👉 `/rekap #[label]` : Melihat total pengeluaran, bagi rata, dan sisa hutang real-time di sesi aktif.\n"; //[cite: 6]
$helpMsg .= "👉 `/history #[label]` : Melihat semua riwayat daftar transaksi yang berstatus aktif.\n"; //[cite: 6]
$helpMsg .= "👉 `/selesai #[label]` : Menutup sesi aktif (Gunakan jika semua hutang di label tersebut sudah lunas).\n\n"; //[cite: 6]

$helpMsg .= "💡 _Tips: Penggunaan tanda pagar (#[label]) bersifat opsional. Jika dikosongkan, bot otomatis mengarahkannya ke label_ `#umum`."; //[cite: 6]

sendMessage($chatId, $helpMsg); //[cite: 6]