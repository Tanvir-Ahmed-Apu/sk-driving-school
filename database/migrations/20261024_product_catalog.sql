CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(32) NOT NULL,
    region VARCHAR(120) NULL,
    name VARCHAR(180) NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    subtitle VARCHAR(255) NULL,
    features JSON NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX products_active_category_order (is_active, category, sort_order),
    INDEX products_region_category (region, category, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (category, region, name, price_cents, subtitle, features, is_active, sort_order)
VALUES
('Basic', NULL, '1 Lesson Package', 24000, NULL, JSON_ARRAY('1 driving lesson (45 minutes)', 'Car for road test', 'Rush road test appointment'), 1, 10),
('Basic', NULL, '2 Lessons Package', 29500, NULL, JSON_ARRAY('2 driving lessons (45 minutes each)', 'Car for road test', 'Rush road test appointment'), 1, 20),
('Basic', NULL, '3 Lessons Package', 35000, NULL, JSON_ARRAY('3 driving lessons (45 minutes each)', 'Car for road test', 'Rush road test appointment'), 1, 30),
('Basic', NULL, '4 Lessons Package', 40500, NULL, JSON_ARRAY('4 driving lessons (45 minutes each)', 'Car for road test', 'Rush road test appointment'), 1, 40),
('Standard', NULL, '5 Lessons Package', 46000, NULL, JSON_ARRAY('5 driving lessons (45 minutes each)', 'Car for road test', 'Rush road test appointment'), 1, 10),
('Standard', NULL, '10 Lessons Package', 73500, NULL, JSON_ARRAY('10 driving lessons (45 minutes each)', 'Car for road test', 'Road test appointment'), 1, 20),
('Standard', NULL, '15 Lessons Package', 101000, NULL, JSON_ARRAY('15 driving lessons (45 minutes each)', 'Car for road test', 'Road test appointment'), 1, 30),
('Individual', NULL, 'Individual Driving Lesson', 5500, '45 minutes', JSON_ARRAY('One private driving lesson'), 1, 10),
('Individual', NULL, 'Rush Road Test', 5000, 'Appointment within 1 week', JSON_ARRAY('Rush road test appointment'), 1, 20),
('Individual', NULL, 'Car for Road Test', 12500, NULL, JSON_ARRAY('Car with a professional instructor'), 1, 30),
('Individual', NULL, 'Highway Driving Lesson', 12000, 'Minimum 2 lessons, 45 minutes per lesson', JSON_ARRAY('Highway driving instruction'), 1, 40);

INSERT INTO products (category, region, name, price_cents, subtitle, features, is_active, sort_order)
SELECT catalog.category, areas.region, catalog.name, catalog.price_cents, catalog.subtitle,
       catalog.features, 1, catalog.sort_order
FROM (
    SELECT category, name, price_cents, subtitle, features, sort_order
    FROM products
    WHERE region IS NULL
) AS catalog
CROSS JOIN (
    SELECT 'Glendale' AS region UNION ALL
    SELECT 'Hempstead' UNION ALL
    SELECT 'Hollis' UNION ALL
    SELECT 'Laurelton' UNION ALL
    SELECT 'Long Island' UNION ALL
    SELECT 'Manhattan' UNION ALL
    SELECT 'Middle Village' UNION ALL
    SELECT 'Queens Village' UNION ALL
    SELECT 'Rego Park' UNION ALL
    SELECT 'Ronkonkoma'
) AS areas;
