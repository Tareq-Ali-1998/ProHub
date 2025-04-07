<?php

require_once dirname(__DIR__) . '/config.php';

/**
 * Class FileHandler
 *
 * Handles basic file operations:
 * - Stores raw file bytes.
 * - Computes file size.
 * - Determines MIME type based on magic bytes.
 * - Provides factory methods for various construction paths.
 */
class FileHandler {
    // Private properties (since we're not using inheritance for extension)
    private $content;   // Raw file content.
    private $size;      // File size in bytes.
    private $mimeType;  // Detected MIME type.

    /**
     * Constructor.
     *
     * @param string $content Raw file content (bytes).
     */
    public function __construct(string $content) {
        $this->content = $content;
        // PHP's strlen() is O(1) since length is stored internally.
        $this->size = strlen($content);
        $this->mimeType = $this->determineMimeType();
    }

    /**
     * Factory method to create a FileHandler from a Base64-encoded string.
     *
     * Usage: FileHandler::fromBase64($base64String);
     *
     * @param string $base64 Base64 encoded file content.
     * @return FileHandler
     * @throws Exception if decoding fails.
     */
    public static function fromBase64(string $base64): FileHandler {
        $content = base64_decode($base64, true);
        if ($content === false) {
            throw new Exception("Invalid Base64 input.");
        }
        return new self($content);
    }

    /**
     * Factory method to create a FileHandler from a file path.
     *
     * Usage: FileHandler::fromPath('/path/to/file');
     *
     * @param string $path File path.
     * @return FileHandler
     * @throws Exception if the file cannot be read.
     */
    public static function fromPath(string $path): FileHandler {
        if (!is_readable($path)) {
            throw new Exception("File not readable at path: $path");
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw new Exception("Failed to read file content.");
        }
        return new self($content);
    }

    /**
     * Determines the MIME type based on the file's magic bytes.
     * In production, consider using finfo_buffer() for more robust detection.
     *
     * @return string The MIME type.
     */
    private function determineMimeType(): string {
        // Check for PDF: "%PDF-" (hex: 25 50 44 46 2D).
        if (strlen($this->content) >= 5 && substr($this->content, 0, 5) === '%PDF-') {
            return 'application/pdf';
        }
        // Check for JPEG: Magic bytes: 0xFF 0xD8 0xFF.
        if (strlen($this->content) >= 3 && substr($this->content, 0, 3) === "\xFF\xD8\xFF") {
            return 'image/jpeg';
        }
        // Check for PNG: Magic bytes: "\x89PNG\x0D\x0A\x1A\x0A".
        if (strlen($this->content) >= 8 && substr($this->content, 0, 8) === "\x89PNG\x0D\x0A\x1A\x0A") {
            return 'image/png';
        }
        // Check for GIF: "GIF87a" or "GIF89a"
        if (strlen($this->content) >= 6 && (substr($this->content, 0, 6) === "GIF87a" || substr($this->content, 0, 6) === "GIF89a")) {
            return 'image/gif';
        }
        // Check for BMP: "BM" at start.
        if (strlen($this->content) >= 2 && substr($this->content, 0, 2) === "BM") {
            return 'image/bmp';
        }
        // Check for TIFF (little endian: "II*" or big endian: "MM\x00\x2A").
        if (strlen($this->content) >= 4) {
            $header = substr($this->content, 0, 4);
            if ($header === "II*\x00" || $header === "MM\x00*") {
                return 'image/tiff';
            }
        }
        // Check for WebP: "RIFF" then "WEBP" at specific positions.
        if (strlen($this->content) >= 12 &&
            substr($this->content, 0, 4) === 'RIFF' &&
            substr($this->content, 8, 4) === 'WEBP') {
            return 'image/webp';
        }
        // Check for ICO: usually starts with 00 00 01 00.
        if (strlen($this->content) >= 4 && bin2hex(substr($this->content, 0, 4)) === '00000100') {
            return 'image/x-icon';
        }
        // Check for SVG: since SVG is XML text, look for the <svg tag.
        if (stripos($this->content, '<svg') !== false) {
            return 'image/svg+xml';
        }
        // Fallback MIME type.
        return 'application/octet-stream';
    }

    // Getter methods.
    public function getContent(): string {
        return $this->content;
    }
    
    public function getSize(): int {
        return $this->size;
    }
    
    public function getMimeType(): string {
        return $this->mimeType;
    }
    
    /**
     * Saves the file content to the specified path.
     *
     * @param string $path Destination file path.
     * @return bool True on success.
     */
    public function save(string $path): bool {
        return file_put_contents($path, $this->content) !== false;
    }
}

/**
 * Class ImageHandler
 *
 * Handles image-specific operations.
 * Uses a FileHandler instance and validates that the file is a valid image.
 */
class ImageHandler {
    private $file;  // Instance of FileHandler.

    /**
     * Constructor.
     *
     * @param FileHandler $file A FileHandler instance.
     * @throws Exception if the file is not a valid image.
     */
    public function __construct(FileHandler $file) {
        // Define allowed image MIME types.
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff',
            'image/webp',
            'image/x-icon',
            'image/svg+xml'
            // You can add more types as needed.
        ];
        $mime = $file->getMimeType();
        if (!in_array($mime, $allowedTypes, true)) {
            throw new Exception("Provided file is not an acceptable image type (found: $mime).");
        }
        // Further validate the image content using getimagesizefromstring()
        // getimagesizefromstring() returns false if it cannot read image dimensions.
        $imgData = @getimagesizefromstring($file->getContent());
        if ($imgData === false) {
            // For SVG, getimagesizefromstring() may not work; do a simple check.
            if ($mime === 'image/svg+xml' && stripos($file->getContent(), '<svg') !== false) {
                // Consider valid for SVG.
            } else {
                throw new Exception("Provided file does not appear to be a valid image.");
            }
        }
        $this->file = $file;
    }

    /**
     * Returns the image's MIME type.
     *
     * @return string
     */
    public function getImageMimeType(): string {
        return $this->file->getMimeType();
    }

    /**
     * Saves the image using the user ID as the filename.
     * The file extension is determined by the image's MIME type.
     *
     * @param string $userId The user ID.
     * @param string $directory Directory for saving the image.
     * @return bool True on success.
     * @throws Exception if the image type is unsupported.
     */
    public function saveImage(string $userId, string $directory): bool {
        $extension = '';
        switch ($this->file->getMimeType()) {
            case 'image/jpeg':
                $extension = '.jpg';
                break;
            case 'image/png':
                $extension = '.png';
                break;
            case 'image/gif':
                $extension = '.gif';
                break;
            case 'image/bmp':
                $extension = '.bmp';
                break;
            case 'image/tiff':
                $extension = '.tiff';
                break;
            case 'image/webp':
                $extension = '.webp';
                break;
            case 'image/x-icon':
                $extension = '.ico';
                break;
            case 'image/svg+xml':
                $extension = '.svg';
                break;
            default:
                throw new Exception("Unsupported image type.");
        }
        $filePath = rtrim($directory, '/') . '/' . $userId . $extension;
        return $this->file->save($filePath);
    }
}

/**
 * Class PDFHandler
 *
 * Handles PDF-specific operations.
 * Uses a FileHandler instance and validates that the file is a valid PDF.
 */
class PDFHandler {
    private $file;  // Instance of FileHandler.

    /**
     * Constructor.
     *
     * @param FileHandler $file A FileHandler instance.
     * @throws Exception if the file is not a valid PDF.
     */
    public function __construct(FileHandler $file) {
        if ($file->getMimeType() !== 'application/pdf') {
            throw new Exception("Provided file is not a PDF based on MIME type.");
        }
        if (!self::isValidPDFFile($file->getContent())) {
            throw new Exception("Provided file content is not a valid PDF.");
        }
        $this->file = $file;
    }

    /**
     * Saves the PDF file using the naming format: userID_pdfName.pdf.
     *
     * @param string $userId The user ID.
     * @param string $pdfName Custom PDF name (without extension).
     * @param string $directory Directory for saving the PDF.
     * @return bool True on success.
     */
    public function savePDF(string $userId, string $pdfName, string $directory): bool {
        $filename = $userId . '_' . $pdfName . '.pdf';
        $filePath = rtrim($directory, '/') . '/' . $filename;
        return $this->file->save($filePath);
    }

    /**
     * Validates if the file content represents a valid PDF.
     * Uses only the magic number check.
     *
     * @param string $content Raw file content.
     * @return bool True if valid, false otherwise.
     */
    public static function isValidPDFFile(string $content): bool {
        if (strlen($content) < 5) {
            return false;
        }
        return bin2hex(substr($content, 0, 5)) === '255044462d';  // "%PDF-"
    }
}

// ============================================================
// Explanation:
//
// 1. Image Validation:
//    - We check the MIME type based on the file's magic bytes.
//    - Then, we use PHP's built-in function getimagesizefromstring()
//      to try to read image dimensions. If it fails, we consider the image invalid,
//      except for SVG files, which are XML-based and checked via the presence of "<svg".
//    - Note: Although it's possible for a user to change the first few bytes,
//      a properly formed image usually has consistent internal structure that getimagesizefromstring()
//      can detect. However, if someone intentionally tampers with the data,
//      no header-only check is 100% foolproof without fully decoding the image.
// 
// 2. PDF Validation:
//    - We rely on checking the first 5 bytes to see if they match "%PDF-".
//    - Without a PDF parser library, this is a basic (but common) method.
// 
// In real-world applications, advanced validation (especially against spoofing)
// might require deeper content analysis, but these methods are typical for many backends.
// ============================================================

// Example Usage:

try {
    // Create a FileHandler from a Base64-encoded image.
    $base64Image = '...'; // Replace with a valid Base64 string for an image.
    $fileHandler = FileHandler::fromBase64($base64Image);
    $imageHandler = new ImageHandler($fileHandler);
    $imageHandler->saveImage('12345', '/path/to/images');

    // Create a FileHandler from a file path for a PDF.
    // $fileHandlerPdf = FileHandler::fromPath('/path/to/document.pdf');
    // $pdfHandler = new PDFHandler($fileHandlerPdf);
    // $pdfHandler->savePDF('12345', 'document', '/path/to/pdfs');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>
