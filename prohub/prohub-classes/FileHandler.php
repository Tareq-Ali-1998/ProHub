<?php

require_once dirname(__DIR__) . '/config.php';

 /**
  * Class FileHandler.
  *
  * Handles basic file operations.
  * - Stores raw file bytes.
  * - Computes file size.
  * - Determines MIME type based on magic bytes.
  * - Provides factory methods for different construction methods.
  */
class FileHandler {
    private $content;   // Raw file content in bytes.
    private $size;      // File size in bytes.
    private $mimeType;  // Detected MIME type.

    /**
     * Constructor.
     *
     * @param string $content Raw file content (bytes).
     */
    public function __construct(string $content) {
        $this->content = $content; // Stores the raw file content.
        $this->size = strlen($content); // Computes the file size in bytes.
        // Determines the MIME type based on magic bytes.
        $this->mimeType = $this->determineMimeType(); 
    }

    /**
     * Factory method to create a FileHandler from a Base64-encoded string.
     *
     * Usage: FileHandler::fromBase64($base64String).
     *
     * @param string $base64 Base64 encoded file content.
     * @return FileHandler.
     * @throws Exception If decoding fails.
     */
    public static function fromBase64(string $base64): FileHandler {
        $content = base64_decode($base64, true); // Decodes the Base64 string.
        if ($content === false) {
            throw new Exception("Invalid Base64 input."); 
        }
        return new self($content); // Returns a new instance of FileHandler.
    }

    /**
     * Factory method to create a FileHandler from a file path.
     *
     * Usage: FileHandler::fromPath('/path/to/file').
     *
     * @param string $path File path.
     * @return FileHandler.
     * @throws Exception If the file cannot be read.
     */
    public static function fromPath(string $path): FileHandler {
        if (!is_readable($path)) {
            throw new Exception("File not readable at path: $path");
        }
        // Reads the file content from the given path.
        $content = file_get_contents($path);
        if ($content === false) {
            throw new Exception("Failed to read file content.");
        }
        return new self($content); // Returns a new instance of FileHandler.
    }

    /**
     * Determines the MIME type based on the file's magic bytes.
     *
     * @return string The MIME type.
     */
    private function determineMimeType(): string {
        // Check for PDF: "%PDF-" (hex: 25 50 44 46 2D).
        if (strlen($this->content) >= 5 && substr($this->content, 0, 5) === '%PDF-') {
            return 'application/pdf'; // Returns the PDF MIME type.
        }
        // Check for JPEG: Magic bytes: 0xFF 0xD8 0xFF.
        if (strlen($this->content) >= 3 && substr($this->content, 0, 3) === "\xFF\xD8\xFF") {
            return 'image/jpg'; // Returns the JPEG MIME type.
        }
        // Check for PNG: Magic bytes: "\x89PNG\x0D\x0A\x1A\x0A".
        if (strlen($this->content) >= 8 &&
            substr($this->content, 0, 8) === "\x89PNG\x0D\x0A\x1A\x0A") {
            return 'image/png'; // Returns the PNG MIME type.
        }
        // Check for GIF: "GIF87a" or "GIF89a".
        if (strlen($this->content) >= 6 &&
            (substr($this->content, 0, 6) === "GIF87a" ||
             substr($this->content, 0, 6) === "GIF89a")) {
            return 'image/gif'; // Returns the GIF MIME type.
        }
        // Check for BMP: "BM" at start.
        if (strlen($this->content) >= 2 && substr($this->content, 0, 2) === "BM") {
            return 'image/bmp'; // Returns the BMP MIME type.
        }
        // Check for TIFF (little endian: "II*" or big endian: "MM\x00\x2A").
        if (strlen($this->content) >= 4) {
            // Retrieves the first four bytes for header validation.
            $header = substr($this->content, 0, 4);
            if ($header === "II*\x00" || $header === "MM\x00*") {
                return 'image/tiff'; // Returns the TIFF MIME type.
            }
        }
        // Check for WebP: "RIFF" then "WEBP" at specific positions.
        if (strlen($this->content) >= 12 &&
            substr($this->content, 0, 4) === 'RIFF' &&
            substr($this->content, 8, 4) === 'WEBP') {
            return 'image/webp'; // Returns the WebP MIME type.
        }
        // Check for ICO: usually starts with 00 00 01 00.
        if (strlen($this->content) >= 4 &&
            bin2hex(substr($this->content, 0, 4)) === '00000100') {
            return 'image/x-icon'; // Returns the ICO MIME type.
        }

        // Fallback MIME type.
        // Returns a generic MIME type if no specific type is matched.
        return 'application/octet-stream';
    }

    /**
     * Returns the raw file content.
     *
     * @return string.
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * Returns the file size in bytes.
     *
     * @return int.
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * Returns the detected MIME type.
     *
     * @return string
     */
    public function getMimeType(): string {
        return $this->mimeType;
    }

    /**
     * Returns a Base64 encoded string of the file content.
     *
     * @return string Base64 encoded file content.
     */
    public function getBase64(): string {
        return base64_encode($this->content);
    }

    /**
     * Checks if the file content is a valid PDF.
     * Validates the PDF by checking if the MIME type is exactly 'application/pdf'.
     *
     * @return bool True if the file is a valid PDF.
     */
    public function isValidPdf(): bool {
        return $this->mimeType === 'application/pdf';
    }

    /**
     * Checks if the file content is a valid image.
     * Validates that the MIME type starts with "image/" and that
     * image dimensions can be determined.
     *
     * @return bool True if the file is a valid image.
     */
    public function isValidImage(): bool {
        // Check if the MIME type starts with "image/".
        if (strpos($this->mimeType, 'image/') !== 0) {
            return false;
        }
        // Attempts to retrieve image dimensions from the content.
        $imgData = @getimagesizefromstring($this->content);
        if ($imgData === false) {
            return false; // Returns false if image dimensions cannot be determined.
        }
        // Returns true if the image is valid according to my current criteria.
        return true;
    }

    /**
     * Saves the file content to the specified path with the given file name.
     * Determines the file extension by taking the part after "/" in the MIME type.
     *
     * @param string $path Destination file path.
     * @param string $fileName File name without extension.
     * @return bool True on success.
     * @throws Exception If saving the file fails.
     */
    public function saveFile(string $path, string $fileName): bool {
        $mime = $this->getMimeType();
        // Splits the MIME type into parts.
        $parts = explode("/", $mime);
        // Uses the part after "/" as the file extension.
        $ext = isset($parts[1]) ? $parts[1] : 'file';
        // Removes any additional qualifiers from the extension.
        $ext = preg_replace('/\+.*/', '', $ext); 
        // Constructs the file extension with a preceding dot.
        $extension = '.' . $ext;
        // For unknown/binary files (MIME: application/octet-stream), avoid
        // appending a generic extension.
        // Leaving it blank prevents misleading extensions like ".file".
        if ($mime === 'application/octet-stream') {
            $extension = '';
        }
        // Constructs the full path with the file name and extension.
        $fullPath = rtrim($path, '/') . "/" . $fileName . $extension;
        // Writes the file content to the specified full path.
        $bytes = @file_put_contents($fullPath, $this->content);
        if ($bytes === false) {
            throw new Exception("Failed to save file to {$fullPath}");
        }
        return true;
    }
}

?>
