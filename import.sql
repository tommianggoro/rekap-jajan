-- 1. Tabel untuk menyimpan data Grup Telegram
CREATE TABLE IF NOT EXISTS `groups` (
  `chat_id` BIGINT PRIMARY KEY,
  `group_name` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel untuk menyimpan data User/Anggota
CREATE TABLE IF NOT EXISTS `members` (
  `user_id` BIGINT,
  `chat_id` BIGINT,
  `first_name` VARCHAR(255),
  PRIMARY KEY (`user_id`, `chat_id`),
  FOREIGN KEY (`chat_id`) REFERENCES `groups`(`chat_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Tabel Sesi (untuk memisahkan rekap per bulan atau per event)
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `chat_id` BIGINT,
  `label` VARCHAR(50) DEFAULT 'umum',
  `status` ENUM('Active', 'Closed') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`chat_id`) REFERENCES `groups`(`chat_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabel Transaksi Pengeluaran
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` INT,
  `paid_by` BIGINT,       -- Siapa yang membayar
  `recorded_by` BIGINT,   -- Siapa yang mencatat (bisa berbeda jika pakai reply)
  `amount` DECIMAL(15, 2),
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`session_id`) REFERENCES `sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;