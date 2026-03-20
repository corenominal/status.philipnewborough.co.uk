<?php

namespace App\Controllers\Api;

use App\Models\MediaModel;
use CodeIgniter\HTTP\ResponseInterface;
use Ramsey\Uuid\Uuid;

class Media extends BaseController
{
    private const ALLOWED_TYPES = [
        'video/mp4'  => 'mp4',
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    private const MAX_FILE_SIZE = 524288000; // 500 MB
    private const MAX_WIDTH     = 1920;

    /**
     * POST /api/media
     * Upload an image or video file (admin only). Returns the created media record.
     */
    public function upload(): ResponseInterface
    {
        if (empty($GLOBALS['is_admin'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden.']);
        }

        $file = $this->request->getFile('file');

        if (! $file || ! $file->isValid()) {
            $errorCode = $file ? $file->getError() : UPLOAD_ERR_NO_FILE;

            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'No valid file uploaded. Upload error code: ' . $errorCode,
            ]);
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'File exceeds the maximum allowed size (500 MB).']);
        }

        // Validate MIME type against the actual file content, not just the
        // client-supplied header, to prevent MIME sniffing attacks.
        $mime = $file->getMimeType();

        if (! array_key_exists($mime, self::ALLOWED_TYPES)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Unsupported file type. Allowed: JPEG, PNG, GIF, WebP, MP4.']);
        }

        $ext         = self::ALLOWED_TYPES[$mime];
        $description = trim((string) ($this->request->getPost('description') ?? ''));
        $uuid        = Uuid::uuid4()->toString();
        $fileName    = $uuid . '.' . $ext;
        $uploadPath  = FCPATH . 'media';

        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0775, true) && ! is_dir($uploadPath)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unable to create media directory.']);
        }

        if (! $file->move($uploadPath, $fileName)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to save uploaded file.']);
        }

        $fullPath = $uploadPath . '/' . $fileName;
        $width    = 0;
        $height   = 0;

        // Correct EXIF orientation and/or resize images wider than MAX_WIDTH.
        if ($mime !== 'video/mp4') {
            try {
                // EXIF orientation is only reliable for JPEG.
                $orientation = 1;

                if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
                    $exifData    = @exif_read_data($fullPath);
                    $orientation = isset($exifData['Orientation']) ? (int) $exifData['Orientation'] : 1;
                }

                $image = \Config\Services::image('gd');
                $image->withFile($fullPath);

                // Track dimensions manually so they stay accurate after any
                // rotation where width and height are swapped.
                $imgWidth  = $image->getWidth();
                $imgHeight = $image->getHeight();

                $changed = false;

                if ($orientation > 1) {
                    // Transform the stored pixels so the image displays correctly,
                    // removing the need for a viewer to respect the EXIF tag.
                    switch ($orientation) {
                        case 2:
                            $image->flip('horizontal');
                            break;

                        case 3:
                            $image->rotate(180);
                            break;

                        case 4:
                            $image->flip('vertical');
                            break;

                        case 5:
                            // Transpose: flip horizontal then rotate 90° CW.
                            $image->flip('horizontal')->rotate(90);
                            [$imgWidth, $imgHeight] = [$imgHeight, $imgWidth];
                            break;

                        case 6:
                            // 90° CW (most common phone portrait orientation).
                            $image->rotate(90);
                            [$imgWidth, $imgHeight] = [$imgHeight, $imgWidth];
                            break;

                        case 7:
                            // Transverse: flip horizontal then rotate 270° CW.
                            $image->flip('horizontal')->rotate(270);
                            [$imgWidth, $imgHeight] = [$imgHeight, $imgWidth];
                            break;

                        case 8:
                            // 270° CW (90° CCW).
                            $image->rotate(270);
                            [$imgWidth, $imgHeight] = [$imgHeight, $imgWidth];
                            break;
                    }

                    $changed = true;
                }

                if ($imgWidth > self::MAX_WIDTH) {
                    $imgHeight = (int) round($imgHeight * self::MAX_WIDTH / $imgWidth);
                    $imgWidth  = self::MAX_WIDTH;
                    $image->resize($imgWidth, $imgHeight, true);
                    $changed = true;
                }

                if ($changed) {
                    $image->save($fullPath);
                }

                $width  = $imgWidth;
                $height = $imgHeight;
            } catch (\Throwable $e) {
                log_message('warning', 'Image processing failed for ' . $fileName . ': ' . $e->getMessage());
            }
        }

        $fileSize = (string) filesize($fullPath);

        $mediaModel = new MediaModel();
        $mediaModel->insert([
            'uuid'        => $uuid,
            'file_name'   => $fileName,
            'description' => $description,
            'file_ext'    => $ext,
            'mime_type'   => $mime,
            'width'       => $width,
            'height'      => $height,
            'filesize'    => $fileSize,
        ]);

        $media = $mediaModel->find($mediaModel->getInsertID());

        return $this->response->setStatusCode(201)->setJSON(['status' => 'success', 'data' => $media]);
    }

    /**
     * DELETE /api/media/:id
     * Delete a media record and its file from disk (admin only).
     */
    public function delete(int $id): ResponseInterface
    {
        if (empty($GLOBALS['is_admin'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden.']);
        }

        $mediaModel = new MediaModel();
        $media      = $mediaModel->find($id);

        if (! $media) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Media not found.']);
        }

        $filePath = FCPATH . 'media/' . basename((string) $media['file_name']);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $mediaModel->delete($id);

        return $this->response->setJSON(['status' => 'success']);
    }
}
