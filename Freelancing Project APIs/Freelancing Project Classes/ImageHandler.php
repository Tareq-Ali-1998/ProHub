<?php

class ImageHandler
{
    /** @var string The fileName of the image. */
    private string $fileName;

    /** @var string The binary data of the image. */
    private string $data;

    /** @var string The MIME type of the image. */
    private string $mimeType;

    /** @var GdImage The GD image resource. */
    private GdImage $img;

    /**
     * ImageHandler constructor.
     *
     * @param string $fileName The file name of the image to load.
     */
    public function __construct(string $fileName) {
        $this->fileName = $fileName;
        $this->data = file_get_contents($fileName);
        $this->mimeType = $this->getMimeType();
        $this->img = $this->createImage();
    }


    private function createImage(): GdImage {
        $img = null;
        switch ($this->mimeType) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($this->fileName);
                break;
            case 'image/png':
                $img = imagecreatefrompng($this->fileName);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($this->fileName);
                break;
            default:
                throw new Exception('Unsupported image type: ' . $this->mimeType);
        }
        return $img;
    }


    /**
     * Compresses the image using JPEG compression.
     *
     * @param int $compressionFactor represents the compression level, between 0 and 100.
     * @return string The compressed image data.
     */
    public function compress(int $compressionFactor = 30): string {
        ob_start();
        imagejpeg($this->img, null, $compressionFactor);
        $compressedImageData = ob_get_clean();
        return $compressedImageData;
    }


    /**
     * Encodes the compressed image data as base64.
     *
     * @param int $compressionFactor represents the compression level, between 0 and 100.
     * @return string ImageDataInBase64Encoded the base64-encoded compressed image data.
     */
    public function toBase64(int $compressionFactor = 30): string {
        $compressedImageData = $this->compress($compressionFactor);
        $ImageDataInBase64Encoded = base64_encode($compressedImageData);
        return $ImageDataInBase64Encoded;
    }


    /**
     * Saves the compressed image data to a file.
     *
     * @param string $fileName The file name of the output file.
     * @param int $compressionFactor the compression level, between 0 and 100.
     * @return bool True on success, false on failure.
     */
    public function save(string $fileName, int $compressionFactor = 30): bool {
        $compressedImageData = $this->compress($compressionFactor);
        return file_put_contents($fileName, $compressedImageData) !== false;
    }

    /**
     * Gets the width of the image in pixels.
     *
     * @return int The width of the image in pixels.
     */
    public function getWidth(): int {
        return imagesx($this->img);
    }

    /**
     * Gets the height of the image in pixels.
     *
     * @return int The height of the image in pixels.
     */
    public function getHeight(): int {
        return imagesy($this->img);
    }

    /**
     * Gets the MIME type of the image.
     *
     * @return string The MIME type of the image.
     */
    public function getMimeType(): string {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($fileInfo, $this->data);
        finfo_close($fileInfo);
        return $mimeType;
    }

    /**
     * Checks if the image is a JPEG image.
     *
     * @return bool True if the image is a JPEG image, false otherwise.
     */
    public function isJpg(): bool {
        return $this->mimeType === 'image/jpeg';
    }

    /**
     * Checks if the image is a PNG image.
     *
     * @return bool True if the image is a PNG image, false otherwise.
     */
    public function isPng(): bool {
        return $this->mimeType === 'image/png';
    }

    /**
     * Checks if the image is a GIF image.
     *
     * @return bool True if the image is a GIF image, false otherwise.
     */
    public function isGif(): bool {
        return $this->mimeType === 'image/gif';
    }

     /**
     * Converts the image to JPEG format.
     *
     * @return bool True on success, false on failure.
     */
    public function convertToJpg(): bool {
        $success = false;
        if ($this->isJpg()) {
            $success = true;
        }
        else {
            $img = $this->img;
            $newImg = imagecreatetruecolor($this->getWidth(), $this->getHeight());
            imagecopy($newImg, $img, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
            ob_start();
            imagejpeg($newImg);
            $this->img = imagecreatefromstring(ob_get_clean());
            $this->mimeType = 'image/jpeg';
            $success = true;
        }
        return $success;
    }

    /**
     * Rotates the image by the specified angle.
     *
     * @param int $angle The angle of rotation, in degrees.
     * @param int $backgroundColor The background color to use for the uncovered area.
     * @return bool True on success, false on failure.
     */
    public function rotate(int $angle, int $backgroundColor = 0): bool {
        $img = $this->img;
        $rotatedImg = imagerotate($img, $angle, $backgroundColor);
        if ($rotatedImg === false) {
            return false;
        }
        else {
            $this->img = $rotatedImg;
            return true;
        }
    }

    /**
     * Resizes the image to the specified dimensions.
     *
     * @param int $width The new width of the image.
     * @param int $height The new height of the image.
     * @return bool True on success, false on failure.
     */
    public function resize(int $width, int $height): bool {
        $img = $this->img;
        $resizedImg = imagescale($img, $width, $height);
        if ($resizedImg === false) {
            return false;
        }
        else {
            $this->img = $resizedImg;
            return true;
        }
    }

    /**
     * Crops the image to the specified dimensions.
     *
     * @param int $x The X-coordinate of the upper-left corner of the crop rectangle.
     * @param int $y The Y-coordinate of the upper-left corner of the crop rectangle.
     * @param int $width The width of the crop rectangle.
     * @param int $height The height of the crop rectangle.
     * @return bool True on success, false on failure.
     */
    public function crop(int $x, int $y, int $width, int $height): bool {
        $img = $this->img;
        $croppedImg = imagecrop($img, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        if ($croppedImg === false) {
            return false;
        }
        else {
            $this->img = $croppedImg;
            return true;
        }
    }

    /**
     * Destroys the GD image resource to free up memory.
     */
    public function destroy() {
        imagedestroy($this->img);
    }

}


?>