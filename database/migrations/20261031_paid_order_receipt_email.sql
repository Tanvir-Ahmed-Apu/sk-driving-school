ALTER TABLE orders
    ADD COLUMN receipt_email_sent_at TIMESTAMP NULL AFTER paid_at;

UPDATE orders
SET receipt_email_sent_at = COALESCE(paid_at, updated_at, created_at)
WHERE payment_status = 'paid' AND receipt_email_sent_at IS NULL;
