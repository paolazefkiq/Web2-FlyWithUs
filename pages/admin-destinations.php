<?php

require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$pageTitle = 'Menaxho Destinacionet';
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');
$formError = '';
$destinations = [];
$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$uploadRelativeDirectory = 'assets/img/destinations';
$uploadDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . 
DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'destinations';
$formData = [
    'city' => '',
    'country' => '',
    'description' => '',
    'image_path' => '',
];
$errors = [
    'city' => '',
    'country' => '',
    'description' => '',
    'image' => '',
];

$validateDestinationForm = static function (array $data): array {
    $errors = [
        'city' => '',
        'country' => '',
        'description' => '',
        'image' => '',
    ];

    if ($data['country'] === '') {
        $errors['country'] = 'Plotësoni shtetin.';
    } elseif (!preg_match('/^[A-Za-zÇçËë\s\-]{2,80}$/', $data['country'])) {
        $errors['country'] = 'Shteti duhet te permbaje vetem shkronja.';
    }

    if ($data['description'] === '') {
        $errors['description'] = 'Plotësoni përshkrimin.';
    } elseif (mb_strlen($data['description']) < 10) {
        $errors['description'] = 'Përshkrimi duhet të ketë së paku 10 karaktere.';
    }

    return $errors;
};

$prepareDestinationImageUpload = static function (?array $file, string $city) use ($uploadRelativeDirectory, $uploadDirectory): array {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [
            'error' => '',
            'relative_path' => null,
            'absolute_path' => null,
        ];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [
            'error' => 'Ngarkimi i imazhit dështoi. Ju lutemi provoni përsëri.',
            'relative_path' => null,
            'absolute_path' => null,
        ];
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return [
            'error' => 'Imazhi duhet të jetë deri në 5MB.',
            'relative_path' => null,
            'absolute_path' => null,
        ];
    }

    $originalName = (string)($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

    if (!in_array($extension, $allowedExtensions, true)) {
        return [
            'error' => 'Lejohen vetëm formatet JPG, JPEG, PNG, WEBP dhe AVIF.',
            'relative_path' => null,
            'absolute_path' => null,
        ];
    }

    $citySlug = strtolower((string)preg_replace('/[^a-z0-9]+/i', '-', $city));
    $citySlug = trim($citySlug, '-');

    if ($citySlug === '') {
        $citySlug = 'destination';
    }

    try {
        $uniquePart = bin2hex(random_bytes(4));
    } catch (Exception $exception) {
        $uniquePart = (string)mt_rand(1000, 9999);
    }

    $fileName = $citySlug . '-' . date('YmdHis') . '-' . $uniquePart . '.' . $extension;
    $relativePath = $uploadRelativeDirectory . '/' . $fileName;
    $absolutePath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

    return [
        'error' => '',
        'relative_path' => $relativePath,
        'absolute_path' => $absolutePath,
    ];
};

$deleteManagedDestinationImage = static function (?string $relativePath) use ($uploadRelativeDirectory): void {
    if (!$relativePath) {
        return;
    }

    $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
    $managedPrefix = rtrim($uploadRelativeDirectory, '/') . '/';

    if (strpos($normalizedPath, $managedPrefix) !== 0) {
        return;
    }

    $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);

    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
};

try {
    $pdo = getPDO();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            $destinationId = (int)($_POST['destination_id'] ?? 0);

            if ($destinationId > 0) {
                $destinationImageStatement = $pdo->prepare(
                    'SELECT image_path
                     FROM destinations
                     WHERE id = :id
                     LIMIT 1'
                );
                $destinationImageStatement->execute(['id' => $destinationId]);
                $destinationRow = $destinationImageStatement->fetch();

                $bookingCountStatement = $pdo->prepare(
                    'SELECT COUNT(*)
                     FROM bookings b
                     INNER JOIN routes r ON r.id = b.route_id
                     WHERE r.destination_id = :destination_id'
                );
                $bookingCountStatement->execute(['destination_id' => $destinationId]);
                $bookingCount = (int)$bookingCountStatement->fetchColumn();

                if ($bookingCount > 0) {
                    setFlash('flash_error', 'Ky destinacion ka rezervime të lidhura dhe nuk mund të fshihet.');
                } else {
                    $deleteStatement = $pdo->prepare('DELETE FROM destinations WHERE id = :id');
                    $deleteStatement->execute(['id' => $destinationId]);
                    $deleteManagedDestinationImage($destinationRow['image_path'] ?? null);
                    setFlash('flash_success', 'Destinacioni dhe rrugët e lidhura u fshinë me sukses.');
                }
            }

            redirect($GLOBALS['base_url'] . '/pages/admin-destinations.php');
        }

        $existingImagePath = '';
        $destinationId = 0;

        if ($action === 'update') {
            $destinationId = (int)($_POST['destination_id'] ?? 0);

            if ($destinationId < 1) {
                setFlash('flash_error', 'Destinacioni nuk u gjet.');
                redirect($GLOBALS['base_url'] . '/pages/admin-destinations.php');
            }

            $existingDestinationStatement = $pdo->prepare(
                'SELECT image_path
                 FROM destinations
                 WHERE id = :id
                 LIMIT 1'
            );
            $existingDestinationStatement->execute(['id' => $destinationId]);
            $existingDestination = $existingDestinationStatement->fetch();

            if (!$existingDestination) {
                setFlash('flash_error', 'Destinacioni nuk u gjet.');
                redirect($GLOBALS['base_url'] . '/pages/admin-destinations.php');
            }

            $existingImagePath = (string)($existingDestination['image_path'] ?? '');
        }
          
         $formData = [
            'city' => trim($_POST['city'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image_path' => $existingImagePath,
        ];
        
         $errors = $validateDestinationForm($formData);
        $imageUpload = $_FILES['destination_image'] ?? null;
        $preparedImageUpload = $prepareDestinationImageUpload($imageUpload, $formData['city']);

        if ($preparedImageUpload['error'] !== '') {
            $errors['image'] = $preparedImageUpload['error'];
        }

        if ($errors['city'] === '' && $formData['city'] !== '') {
            $duplicateDestinationStatement = $pdo->prepare(
                'SELECT id
                 FROM destinations
                 WHERE city = :city' . ($action === 'update' ? ' AND id <> :id' : '') . '
                 LIMIT 1'
            );
            $duplicateDestinationParams = ['city' => $formData['city']];

            if ($action === 'update') {
                $duplicateDestinationParams['id'] = $destinationId;
            }

            $duplicateDestinationStatement->execute($duplicateDestinationParams);

            if ($duplicateDestinationStatement->fetch()) {
                $errors['city'] = 'Ky qytet ekziston tashme si destinacion.';
            }
        }

         if (!array_filter($errors)) {
            $storedImagePath = $existingImagePath;
            $newUploadedImagePath = null;

            if ($preparedImageUpload['relative_path'] !== null) {
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                    $formError = 'Folderi i imazheve nuk u krijua. Provoni përsëri.';
                } elseif (!move_uploaded_file($imageUpload['tmp_name'], $preparedImageUpload['absolute_path'])) {
                    $formError = 'Imazhi nuk u ruajt. Ju lutemi provoni përsëri.';
                } else {
                    $storedImagePath = $preparedImageUpload['relative_path'];
                    $newUploadedImagePath = $preparedImageUpload['relative_path'];
                    $formData['image_path'] = $storedImagePath;
                }
            }

            if ($formError === '') {
                try {
                    if ($action === 'update') {
                        $statement = $pdo->prepare(
                            'UPDATE destinations
                             SET city = :city,
                                 country = :country,
                                 description = :description,
                                 image_path = :image_path
                             WHERE id = :id'
                        );
                        $statement->execute([
                            'city' => $formData['city'],
                            'country' => $formData['country'],
                            'description' => $formData['description'],
                            'image_path' => $storedImagePath !== '' ? $storedImagePath : null,
                            'id' => $destinationId,
                        ]);

                         if ($newUploadedImagePath !== null && $existingImagePath !== '' && $existingImagePath !== $newUploadedImagePath) {
                            $deleteManagedDestinationImage($existingImagePath);
                        }

                        setFlash('flash_success', 'Destinacioni u përditësua me sukses.');
                    } else {
                        $statement = $pdo->prepare(
                            'INSERT INTO destinations (city, country, description, image_path)
                             VALUES (:city, :country, :description, :image_path)'
                        );
                        $statement->execute([
                            'city' => $formData['city'],
                            'country' => $formData['country'],
                            'description' => $formData['description'],
                            'image_path' => $storedImagePath !== '' ? $storedImagePath : null,
                        ]);
                        setFlash('flash_success', 'Destinacioni u shtua me sukses. Tani shtoni rrugen te "Menaxho rruget" qe te shfaqet ne oferta dhe rezervim.');
                    }

                    redirect($GLOBALS['base_url'] . '/pages/admin-destinations.php');
                } catch (PDOException $exception) {
                    if ($newUploadedImagePath !== null) {
                        $deleteManagedDestinationImage($newUploadedImagePath);
                    }

                    $formError = 'Destinacioni nuk u ruajt. Ju lutemi kontrolloni të dhënat dhe provoni përsëri.';
                }
            }
        }
    }
 }
    
    
