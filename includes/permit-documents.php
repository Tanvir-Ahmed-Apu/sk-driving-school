<?php

const MAX_PERMIT_IMAGE_BYTES = 5242880;

function permitUploadDirectory(): string
{
    $configuredDirectory = appEnv('PRIVATE_UPLOAD_DIR');

    return $configuredDirectory !== ''
        ? rtrim($configuredDirectory, DIRECTORY_SEPARATOR)
        : dirname(__DIR__, 2) . '/sk-driving-school-private/permits';
}

function validatePermitImageUploads(array $files): array
{
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $uploads = [];

    foreach (['front', 'back'] as $side) {
        $file = $files['permit_' . $side] ?? null;
        if (
            !is_array($file)
            || !isset($file['error'], $file['tmp_name'], $file['size'])
            || !is_int($file['error'])
            || !is_string($file['tmp_name'])
            || !is_int($file['size'])
        ) {
            throw new RuntimeException('Upload clear photos of both the front and back of your learner permit.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
                    ? 'Each learner permit photo must be 5 MB or smaller.'
                    : 'Both learner permit photos are required. Please choose the front and back images again.'
            );
        }
        if ($file['size'] < 1 || $file['size'] > MAX_PERMIT_IMAGE_BYTES || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Each learner permit photo must be a valid image no larger than 5 MB.');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($file['tmp_name']);
        $imageInfo = @getimagesize($file['tmp_name']);
        if (
            !is_string($mimeType)
            || !in_array($mimeType, $allowedMimeTypes, true)
            || !is_array($imageInfo)
            || ($imageInfo['mime'] ?? null) !== $mimeType
            || (int) ($imageInfo[0] ?? 0) < 1
            || (int) ($imageInfo[1] ?? 0) < 1
            || (int) $imageInfo[0] * (int) $imageInfo[1] > 16000000
        ) {
            throw new RuntimeException('Use a clear JPEG, PNG, or WebP image for each side of your learner permit.');
        }

        $uploads[$side] = [
            'tmp_name' => $file['tmp_name'],
            'mime_type' => $mimeType,
        ];
    }

    return $uploads;
}

function storePermitImageUploads(array $uploads): array
{
    $directory = permitUploadDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Secure upload storage is unavailable. Please contact the school.');
    }

    $realDirectory = realpath($directory);
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) && is_string($_SERVER['DOCUMENT_ROOT'])
        ? realpath($_SERVER['DOCUMENT_ROOT'])
        : false;
    if ($realDirectory === false) {
        throw new RuntimeException('Secure upload storage is unavailable. Please contact the school.');
    }
    if (
        $documentRoot !== false
        && ($realDirectory === $documentRoot
            || str_starts_with($realDirectory, rtrim($documentRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR))
    ) {
        throw new RuntimeException('Secure upload storage must be configured outside the public website directory.');
    }
    if (!chmod($realDirectory, 0700)) {
        throw new RuntimeException('Secure upload storage permissions could not be restricted. Please contact the school.');
    }

    $stored = [];
    foreach ($uploads as $side => $upload) {
        $storageKey = bin2hex(random_bytes(32));
        $path = $realDirectory . DIRECTORY_SEPARATOR . $storageKey;
        if (!move_uploaded_file($upload['tmp_name'], $path)) {
            deletePermitImageFiles(array_column($stored, 'storage_key'));
            throw new RuntimeException('A learner permit photo could not be stored securely. Please try again.');
        }
        if (!chmod($path, 0600)) {
            unlink($path);
            deletePermitImageFiles(array_column($stored, 'storage_key'));
            throw new RuntimeException('A learner permit photo could not be stored securely. Please contact the school.');
        }

        $stored[$side] = [
            'storage_key' => $storageKey,
            'mime_type' => $upload['mime_type'],
        ];
    }

    return $stored;
}

function deletePermitImageFiles(array $storageKeys): void
{
    $directory = permitUploadDirectory();
    foreach ($storageKeys as $storageKey) {
        if (
            is_string($storageKey)
            && preg_match('/\A[a-f0-9]{64}\z/', $storageKey) === 1
            && is_file($directory . DIRECTORY_SEPARATOR . $storageKey)
            && !unlink($directory . DIRECTORY_SEPARATOR . $storageKey)
        ) {
            error_log('Could not remove an unlinked learner permit upload.');
        }
    }
}
