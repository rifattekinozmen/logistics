-- SQL Server: Teslimat import kayıtlarını sıfırla, bir sonraki ID 1 olsun
-- SSMS veya sqlcmd ile logistics veritabanında çalıştırın.

-- 1) delivery_numbers'daki import_batch_id'yi null yap (FK yüzünden önce)
UPDATE delivery_numbers SET import_batch_id = NULL WHERE import_batch_id IS NOT NULL;

-- 2) Rapor satırlarını sil
DELETE FROM delivery_report_rows;

-- 3) Import batch kayıtlarını sil
DELETE FROM delivery_import_batches;

-- 4) Identity'yı sıfırla (bir sonraki INSERT id=1 alacak)
DBCC CHECKIDENT (delivery_import_batches, RESEED, 0);
