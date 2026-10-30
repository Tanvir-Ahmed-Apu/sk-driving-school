-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin login failures table
CREATE TABLE IF NOT EXISTS admin_login_failures (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    remote_key CHAR(64) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX admin_login_failures_key_time (remote_key, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table (core orders)
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(24) NOT NULL UNIQUE,
    public_token CHAR(64) NOT NULL UNIQUE,
    checkout_token CHAR(64) NOT NULL UNIQUE,
    customer_first_name VARCHAR(100) NOT NULL,
    customer_last_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(254) NOT NULL,
    customer_phone VARCHAR(40) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(120) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    preferred_date DATE DEFAULT NULL,
    preferred_time VARCHAR(40) DEFAULT NULL,
    instructor_preference VARCHAR(120) DEFAULT NULL,
    course_date DATE DEFAULT NULL,
    subtotal_cents INT UNSIGNED NOT NULL DEFAULT 0,
    discount_cents INT UNSIGNED NOT NULL DEFAULT 0,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    paid_at TIMESTAMP NULL,
    receipt_email_sent_at TIMESTAMP NULL,
    terms_accepted_at TIMESTAMP NULL,
    order_status VARCHAR(32) NOT NULL DEFAULT 'awaiting_payment',
    payment_status VARCHAR(32) NOT NULL DEFAULT 'pending',
    square_order_id VARCHAR(64) UNIQUE NULL,
    square_payment_link_id VARCHAR(64) UNIQUE NULL,
    square_payment_url VARCHAR(2048) NULL,
    square_payment_id VARCHAR(64) UNIQUE NULL,
    customer_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    INDEX orders_payment_status_idx (payment_status),
    INDEX orders_order_status_idx (order_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order documents (learner permit photos)
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

-- Order events (approval/decline audit log)
CREATE TABLE IF NOT EXISTS order_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    admin_user_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(80) NOT NULL,
    details VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    INDEX order_events_order_id (order_id),
    INDEX order_events_admin_user_id (admin_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items
CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(180) NOT NULL,
    category VARCHAR(80) NOT NULL,
    region VARCHAR(120) DEFAULT NULL,
    quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price_cents INT UNSIGNED NOT NULL DEFAULT 0,
    line_total_cents INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX order_items_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products/catalog
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    price_cents INT UNSIGNED NOT NULL,
    region VARCHAR(120) DEFAULT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Package table
CREATE TABLE IF NOT EXISTS packages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    price_cents INT UNSIGNED NOT NULL,
    region VARCHAR(120) DEFAULT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Package prices (junction table)
CREATE TABLE IF NOT EXISTS package_price (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    package_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    UNIQUE KEY package_product (package_id, product_id),
    INDEX package_price_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(254) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX contact_messages_created_at (created_at),
    INDEX contact_messages_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter subscriptions
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL UNIQUE,
    status VARCHAR(20) NOT NULL DEFAULT 'subscribed',
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    INDEX newsletter_status_subscribed (status, subscribed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
