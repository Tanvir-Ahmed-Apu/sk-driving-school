CREATE TABLE IF NOT EXISTS order_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    document_side VARCHAR(8) NOT NULL,
    storage_key CHAR(64) NOT NULL UNIQUE,
    mime_type VARCHAR(32) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT order_documents_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT order_documents_side_check CHECK (document_side IN ('front', 'back')),
    UNIQUE KEY order_documents_order_side (order_id, document_side),
    INDEX order_documents_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
