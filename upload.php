<?php
require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order = explode(',', $_POST['order']);
    $uploads = $_FILES['pdfs'];

    // Verifica se há arquivos enviados
    if (empty($uploads['name'][0])) {
        die('No files uploaded.');
    }

    // Diretório temporário para salvar os arquivos enviados
    $tempDir = 'uploads/';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Mover os arquivos enviados para o diretório temporário
    $files = [];
    foreach ($uploads['tmp_name'] as $key => $tmpName) {
        if ($uploads['error'][$key] !== UPLOAD_ERR_OK) {
            die('Error during file upload: ' . $uploads['name'][$key]);
        }

        $filename = basename($uploads['name'][$key]);
        $targetFile = $tempDir . $filename;
        if (move_uploaded_file($tmpName, $targetFile)) {
            $files[] = $targetFile;
        } else {
            die('Failed to move uploaded file: ' . $filename);
        }
    }

    // Ordenar os arquivos com base na entrada do usuário
    $orderedFiles = [];
    foreach ($order as $index) {
        if (!isset($files[$index - 1])) {
            die('Invalid order index: ' . $index);
        }
        $orderedFiles[] = $files[$index - 1];
    }

    // Criar o PDF mesclado
    $pdf = new Fpdi();
    foreach ($orderedFiles as $file) {
        if (!file_exists($file)) {
            die('File not found: ' . $file);
        }

        $pageCount = $pdf->setSourceFile($file);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }
    }

    // Salvar o PDF mesclado no diretório temporário
    $mergedFile = $tempDir . 'merged.pdf';
    $pdf->Output('F', $mergedFile);

    // Forçar o download do PDF mesclado
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="merged.pdf"');
    readfile($mergedFile);

    // Limpar os arquivos temporários
    array_map('unlink', glob("$tempDir*"));
    rmdir($tempDir);
}