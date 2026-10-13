<?php

declare(strict_types=1);

namespace App\Services;

/** Upload de imagens de produto (US09 RN#2: JPG/PNG de até 2 MB). */
final class ImageUploader
{
    public const MAX_BYTES = 2 * 1024 * 1024;
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    public function __construct(private readonly string $directory = ROOT_PATH . '/public/uploads/produtos')
    {
    }

    /** Verifica o arquivo sem movê-lo. @return string|null mensagem de erro */
    public function validate(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_OK) === UPLOAD_ERR_INI_SIZE || ($file['error'] ?? 0) === UPLOAD_ERR_FORM_SIZE) {
            return 'A imagem deve ter no máximo 2 MB.';
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_file($file['tmp_name'] ?? '')) {
            return 'Não foi possível receber a imagem. Tente novamente.';
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES || filesize($file['tmp_name']) > self::MAX_BYTES) {
            return 'A imagem deve ter no máximo 2 MB.';
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::TYPES[$mime])) {
            return 'Formato inválido. Envie uma imagem JPG ou PNG.';
        }
        return null;
    }

    /** Move o arquivo validado e devolve o caminho relativo (uploads/produtos/arquivo.ext). */
    public function store(array $file): string
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $name = bin2hex(random_bytes(8)) . '.' . self::TYPES[$mime];
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }
        $target = $this->directory . '/' . $name;
        $moved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $target)
            : rename($file['tmp_name'], $target);
        if (!$moved) {
            throw new \RuntimeException('Falha ao salvar a imagem enviada.');
        }
        return 'uploads/produtos/' . $name;
    }
}
