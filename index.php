<?php

function get_sorted_destinations(PDO $pdo): array 
{
    $statement = $pdo->prepare(
        'SELECT d.id, d.city, d.country, d.description, d.image_path, MIN(r.price) AS min_price
         FROM destinations d
         INNER JOIN routes r ON r.destination_id = d.id
         GROUP BY d.id, d.city, d.country, d.description, d.image_path
         ORDER BY d.city ASC'
    );
    $statement->execute();
    $destinations = $statement->fetchAll();

    usort($destinations, static function (array $left, array $right): int {
        $leftPrice = (float)$left['min_price'];
        $rightPrice = (float)$right['min_price'];

        if ($leftPrice === $rightPrice) {
            return strcasecmp((string)$left['city'], (string)$right['city']);
        }

        return $leftPrice <=> $rightPrice;
    });

    return $destinations;
}