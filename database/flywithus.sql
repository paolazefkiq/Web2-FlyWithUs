DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `origin_cities`;
DROP TABLE IF EXISTS `destinations`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_users_email` (`email`),
    UNIQUE KEY `unique_users_username` (`username`)
) 

CREATE TABLE `destinations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `city` VARCHAR(80) NOT NULL,
    `country` VARCHAR(80) NOT NULL,
    `description` TEXT NOT NULL,
    `image_path` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_destinations_city` (`city`)
) 

CREATE TABLE `origin_cities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `city` VARCHAR(80) NOT NULL,
    `country` VARCHAR(80) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_origin_cities_city` (`city`)
) 

CREATE TABLE `routes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `origin_city_id` INT UNSIGNED NOT NULL,
    `destination_id` INT UNSIGNED NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_routes_origin_destination` (`origin_city_id`, `destination_id`),
    KEY `idx_routes_origin_city_id` (`origin_city_id`),
    KEY `idx_routes_destination_id` (`destination_id`),
    CONSTRAINT `fk_routes_origin_city`
        FOREIGN KEY (`origin_city_id`) REFERENCES `origin_cities` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_routes_destination`
        FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) 

CREATE TABLE `bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `route_id` INT UNSIGNED NOT NULL,
    `departure_date` DATE NOT NULL,
    `return_date` DATE DEFAULT NULL,
    `passengers_count` INT UNSIGNED NOT NULL,
    `total_price` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_bookings_user_id` (`user_id`),
    KEY `idx_bookings_route_id` (`route_id`),
    CONSTRAINT `fk_bookings_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_bookings_route`
        FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) 

CREATE TABLE `contact_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_contact_messages_user_id` (`user_id`),
    CONSTRAINT `fk_contact_messages_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
)
