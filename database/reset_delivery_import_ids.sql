-- Teslimat import kayıtlarını sıfırla, ID 1'den başlasın
-- Sıra önemli: önce FK'ya bağlı sütunu null'la, sonra satırları sil.

-- 1) delivery_numbers'daki import_batch_id'yi null yap (FK yüzünden önce)
UPDATE delivery_numbers SET import_batch_id = NULL WHERE import_batch_id IS NOT NULL;

-- 2) Rapor satırlarını sil (batch'e bağlı)
DELETE FROM delivery_report_rows;

-- 3) Import batch kayıtlarını sil
DELETE FROM delivery_import_batches;

-- 4) Auto-increment'ı 1'e al (MySQL / MariaDB)
ALTER TABLE delivery_import_batches AUTO_INCREMENT = 1;
