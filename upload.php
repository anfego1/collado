<?php
// Activar reporte de errores para depuración (eliminar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración para permitir archivos grandes y ejecución prolongada
ini_set('max_execution_time', 3600); // 1 hora (3600 segundos)
ini_set('upload_max_filesize', '1G'); // Tamaño máximo de archivo permitido (1GB)
ini_set('post_max_size', '1G'); // Tamaño máximo de POST permitido (1GB)
ini_set('memory_limit', '2G'); // Memoria disponible para el script (2GB)
ini_set('max_input_time', 3600); // Tiempo máximo de procesamiento de entrada

$uploadDir = 'uploads/';

// Crear la carpeta si no existe
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'video/mp4',
    'video/mpeg',
    'video/ogg',
    'video/webm',
    'video/quicktime'
];

$response = [];

if (!empty($_FILES['file']['name'][0])) {
    foreach ($_FILES['file']['name'] as $key => $fileName) {
        $fileTmpName = $_FILES['file']['tmp_name'][$key];
        $fileSize = $_FILES['file']['size'][$key];

        // Verificar si `mime_content_type()` es válido
        if (!file_exists($fileTmpName) || !is_uploaded_file($fileTmpName)) {
            $response[] = ['status' => 'error', 'message' => "Error al acceder a $fileName."];
            continue;
        }

        // Obtener el tipo MIME
        $fileType = mime_content_type($fileTmpName);

        // Validar tipo de archivo
        if (!in_array($fileType, $allowedMimeTypes)) {
            $response[] = ['status' => 'error', 'message' => "Archivo $fileName no permitido ($fileType)."];
            continue;
        }

        // Generar un nombre único para evitar sobrescribir
        $uniqueName = time() . '_' . uniqid() . '_' . $fileName;
        $destination = $uploadDir . $uniqueName;

        // Intentar mover el archivo
        if (move_uploaded_file($fileTmpName, $destination)) {
            $response[] = ['status' => 'success', 'message' => "Archivo $fileName subido con éxito.", 'file' => $destination];
        } else {
            $response[] = ['status' => 'error', 'message' => "Error al subir el archivo $fileName."];
        }
    }
} else {
    $response[] = ['status' => 'error', 'message' => 'No se han subido archivos.'];
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
