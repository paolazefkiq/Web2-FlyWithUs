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

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password_hash`, `role`, `last_login_at`) VALUES
    (1, 'Customer FlyWithUs', 'customer@flywithus.com', 'customer1', '$2y$10$oLuVr7W1SExGgCWrjzUKMO8kjbYxcmj.Gjc.MHbmQXjvJBPXMAovi', 'customer', NULL),
    (2, 'Admin FlyWithUs', 'admin@flywithus.com', 'admin1', '$2y$10$dXG6QdkjVOjaDwvPunblL.DuUv6TW/jXWPdyoqQkoz6BzDQHbRH..', 'admin', NULL),
    (3, 'Customer 2', 'customer2@flywithus.com', 'customer2', '$2y$10$Y/AvwuCxslc6IwKIOht0muR/UCNrTbnX8A5b7K.Ak7whX1wYNPfcC', 'customer', NULL);

INSERT INTO `destinations` (`id`, `city`, `country`, `description`, `image_path`) VALUES
    (1, 'New York', 'USA', 'Qytet ikonik për udhëtime, kulturë dhe atraksione të njohura botërore.', 'assets/img/newyork.jpg'),
    (2, 'Paris', 'France', 'Destinacion romantik me muze, ushqim të shkëlqyer dhe monumente të famshme.', 'assets/img/paris.jpg'),
    (3, 'Tokyo', 'Japan', 'Përzierje unike mes traditës dhe teknologjisë moderne.', 'assets/img/tokyo.jpg'),
    (4, 'Dubai', 'United Arab Emirates', 'Qytet modern me luks, plazhe dhe qendra të mëdha tregtare.', NULL),
    (5, 'Berlin', 'Germany', 'Qytet historik me art, kulturë dhe jetë të gjallë urbane.', NULL),
    (6, 'London', 'United Kingdom', 'Metropol i madh me muze, teatër dhe destinacione turistike të njohura.', NULL),
    (7, 'Rome', 'Italy', 'Qytet klasik me histori, arkitekturë dhe kuzhinë tradicionale.', NULL),
    (8, 'Barcelona', 'Spain', 'Qytet mesdhetar me arkitekturë ikonike, plazhe dhe jetë të gjallë urbane.', 'assets/img/barcelona.jpg');

INSERT INTO `origin_cities` (`id`, `city`, `country`) VALUES
    (1, 'Prishtina', 'Kosovo'),
    (2, 'Tirana', 'Albania'),
    (3, 'Shkup', 'North Macedonia'),
    (4, 'Podgorica', 'Montenegro'),
    (5, 'Sarajevo', 'Bosnia and Herzegovina');

INSERT INTO `routes` (`id`, `origin_city_id`, `destination_id`, `price`) VALUES
    (1, 1, 1, 399.00),
    (2, 1, 2, 299.00),
    (3, 1, 3, 749.00),
    (4, 1, 4, 499.00),
    (5, 1, 5, 279.00),
    (6, 1, 6, 350.00),
    (7, 1, 7, 320.00),
    (8, 2, 1, 420.00),
    (9, 2, 2, 310.00),
    (10, 2, 3, 770.00),
    (11, 2, 4, 520.00),
    (12, 2, 5, 290.00),
    (13, 2, 6, 360.00),
    (14, 2, 7, 330.00),
    (15, 3, 1, 430.00),
    (16, 3, 2, 320.00),
    (17, 3, 3, 780.00),
    (18, 3, 4, 530.00),
    (19, 3, 5, 295.00),
    (20, 3, 6, 370.00),
    (21, 3, 7, 335.00),
    (22, 4, 1, 450.00),
    (23, 4, 2, 340.00),
    (24, 4, 3, 800.00),
    (25, 4, 4, 550.00),
    (26, 4, 5, 310.00),
    (27, 4, 6, 380.00),
    (28, 4, 7, 350.00),
    (29, 5, 1, 440.00),
    (30, 5, 2, 330.00),
    (31, 5, 3, 790.00),
    (32, 5, 4, 540.00),
    (33, 5, 5, 305.00),
    (34, 5, 6, 375.00),
    (35, 5, 7, 345.00),
    (36, 1, 8, 315.00),
    (37, 2, 8, 325.00),
    (38, 3, 8, 330.00),
    (39, 4, 8, 345.00),
    (40, 5, 8, 335.00);

INSERT INTO `bookings` (`id`, `user_id`, `route_id`, `departure_date`, `return_date`, `passengers_count`, `total_price`, `status`) VALUES
    (1, 1, 2, '2026-06-10', '2026-06-17', 2, 1196.00, 'active');

INSERT INTO `contact_messages` (`id`, `user_id`, `name`, `email`, `subject`, `message`) VALUES
    (1, 1, 'Customer FlyWithUs', 'customer@flywithus.com', 'Pyetje për bagazhin', 'A mund të shtoj bagazh ekstra pasi ta bëj rezervimin?');
