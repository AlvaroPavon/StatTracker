<?php namespace App;

class Uploader {
    
    private string $uploadDir;
    private array $allowedTypes = ['image/jpeg', 'image/png'];
    private int $maxSize = 2 * 1024 * 1024; // 2 MB
    private ?string $error = null;

    public function __construct(string $uploadDir) {
        // Asegurarse de que el directorio de subida existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $this->uploadDir = rtrim($uploadDir, '/'); // Quitar la barra final si existe
    }

    public function getError(): ?string {
        return $this->error;
    }

    /**
     * Sube un archivo. Devuelve el nuevo nombre de archivo si tiene éxito, o null si falla.
     */
    public function upload(array $file, string $baseFileName = 'file'): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error = "Error en la subida del archivo (código: {$file['error']}).";
            return null;
        }

        // 1. Validar Tipo
        if (!in_array($file['type'], $this->allowedTypes)) {
            $this->error = 'Formato de imagen no válido. Solo se permite JPG o PNG.';
            return null;
        }

        // 2. Validar Tamaño
        if ($file['size'] > $this->maxSize) {
            $this->error = 'La imagen es demasiado grande (máx 2MB).';
            return null;
        }

        // 3. Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = $baseFileName . '_' . uniqid() . '.' . $extension;
        $uploadPath = $this->uploadDir . '/' . $newFileName;

        // 4. Mover el archivo
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log('Error al mover el archivo subido a: ' . $uploadPath);
            $this->error = 'Error interno al guardar la imagen.';
            return null;
        }

        return $newFileName; // Éxito
    }
}