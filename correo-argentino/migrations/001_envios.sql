-- ============================================================
-- BTLDECO — Migration 001: tabla correo_argentino_envios
-- ============================================================
-- Aplicar:
--   mysql -u <user> -p <database> < 001_envios.sql
-- O desde phpMyAdmin / hPanel pegando el bloque CREATE TABLE.
--
-- Idempotente: usa CREATE TABLE IF NOT EXISTS.
-- ============================================================

CREATE TABLE IF NOT EXISTS correo_argentino_envios (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    order_id            INT NOT NULL,
    tracking_number     VARCHAR(40) UNIQUE,
    seller_id           VARCHAR(30),
    delivery_type       ENUM('homeDelivery','agency','locker'),
    agency_id           VARCHAR(20) NULL,
    service_type        VARCHAR(5),
    declared_value      DECIMAL(12,2),
    weight_grams        INT,
    status_code         VARCHAR(20),
    status_description  VARCHAR(255),
    label_pdf_base64    LONGTEXT NULL,
    raw_alta_response   JSON NULL,
    raw_last_tracking   JSON NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order  (order_id),
    INDEX idx_status (status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
