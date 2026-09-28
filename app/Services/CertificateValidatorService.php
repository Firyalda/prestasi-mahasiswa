<?php
namespace App\Services;

use App\Core\Database;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Exception;
use PDO;

class CertificateValidatorService {
    private PDO $db;
    private string $tesseractPath;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->tesseractPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
    }

    public function extractQRCode(string $imagePath): ?string {
        if (!file_exists($imagePath)) {
            return null;
        }

        try {
            $qrCode = new QRCode();
            $qrText = $qrCode->readFromFile($imagePath);
            return $qrText ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function verifyViaQRCode(string $qrText): array {
        $stmt = $this->db->prepare("SELECT * FROM official_certificates WHERE qr_code_text = :qrText OR certificate_number = :qrText LIMIT 1");
        $stmt->execute(['qrText' => $qrText]);
        $data = $stmt->fetch();

        if ($data) {
            return [
                'verified' => true,
                'method_used' => 'QR Code',
                'data' => $data,
                'recommendation' => 'LOLOS',
                'message' => '✅ Sertifikat ASLI terverifikasi'
            ];
        }

        return [
            'verified' => false,
            'method_used' => 'QR Code',
            'score' => 0,
            'recommendation' => 'DITOLAK',
            'message' => '❌ QR Code tidak ditemukan dalam database'
        ];
    }

    private function preprocessImage(string $sourcePath): string {
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $image = imagecreatefrompng($sourcePath);
                break;
            default:
                return $sourcePath;
        }

        if (!$image) return $sourcePath;

        imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagefilter($image, IMG_FILTER_CONTRAST, -30);

        $tempDir = __DIR__ . '/../../uploads/temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $processedPath = $tempDir . 'processed_' . time() . '_' . uniqid() . '.png';
        imagepng($image, $processedPath);
        imagedestroy($image);

        return $processedPath;
    }

    public function extractTextWithOCR(string $imagePath): string {
        $processedPath = $this->preprocessImage($imagePath);

        try {
            $ocr = new TesseractOCR($processedPath);
            $ocr->executable($this->tesseractPath);
            $ocr->lang('ind');
            $ocr->dpi(300);
            $text = $ocr->run();

            @unlink($processedPath);
            return $text;
        } catch (Exception $e) {
            @unlink($processedPath);
            return "Error: " . $e->getMessage();
        }
    }

    public function parseCertificateData(string $text): array {
        $data = [
            'nama' => null,
            'nim' => null,
            'juara' => null,
            'tingkat' => null
        ];

        $namaPatterns = [
            '/kepada\s*:\s*([A-Z\s\.]+)/i',
            '/nama\s*:\s*([A-Z\s\.]+)/i',
            '/diberikan kepada\s*:\s*([A-Z\s\.]+)/i'
        ];

        foreach ($namaPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data['nama'] = trim(preg_replace('/\s+/', ' ', $matches[1]));
                break;
            }
        }

        if (preg_match('/juara\s+(\d+)/i', $text, $matches)) {
            $data['juara'] = 'Juara ' . $matches[1];
        } elseif (preg_match('/finalis/i', $text)) {
            $data['juara'] = 'Finalis';
        }

        $tingkatList = ['Lokal', 'Provinsi', 'Nasional', 'Internasional'];
        foreach ($tingkatList as $tingkat) {
            if (stripos($text, $tingkat) !== false) {
                $data['tingkat'] = $tingkat;
                break;
            }
        }

        if (preg_match('/\b\d{8,10}\b/', $text, $matches)) {
            $data['nim'] = $matches[0];
        }

        return $data;
    }

    public function calculateAuthenticityScore(string $text, array $parsedData): array {
        $score = 0;
        $warnings = [];

        $textLength = strlen(trim($text));
        if ($textLength > 200) {
            $score += 30;
        } elseif ($textLength > 100) {
            $score += 20;
        } elseif ($textLength > 50) {
            $score += 10;
        } else {
            $warnings[] = "Teks yang terdeteksi sangat pendek";
        }

        $keywords = ['sertifikat', 'piagam', 'penghargaan', 'diberikan', 'kepada', 'juara'];
        $foundKeywords = 0;
        foreach ($keywords as $keyword) {
            if (stripos($text, $keyword) !== false) $foundKeywords++;
        }
        $score += min($foundKeywords * 8, 40);

        if ($foundKeywords < 2) {
            $warnings[] = "Tidak terdeteksi keyword sertifikat yang cukup";
        }

        if ($parsedData['nama']) $score += 15;
        if ($parsedData['juara']) $score += 10;
        if ($parsedData['tingkat']) $score += 10;

        return [
            'score' => min($score, 100),
            'warnings' => $warnings,
            'confidence' => $score >= 70 ? 'high' : ($score >= 40 ? 'medium' : 'low')
        ];
    }

    public function validateCertificate(string $filePath, int $pengajuanId = 0): array {
        $result = [
            'verified' => false,
            'method_used' => null,
            'score' => 0,
            'data' => null,
            'warnings' => [],
            'recommendation' => 'PERLU DICEK MANUAL'
        ];

        $qrText = $this->extractQRCode($filePath);

        if ($qrText) {
            $qrResult = $this->verifyViaQRCode($qrText);
            if ($qrResult['verified']) {
                $result['verified'] = true;
                $result['method_used'] = 'QR Code';
                $result['score'] = 100;
                $result['data'] = $qrResult['data'];
                $result['recommendation'] = 'LOLOS';
                return $result;
            }
        }

        $extractedText = $this->extractTextWithOCR($filePath);
        $parsedData = $this->parseCertificateData($extractedText);
        $scoreResult = $this->calculateAuthenticityScore($extractedText, $parsedData);

        $result['verified'] = $scoreResult['score'] >= 70;
        $result['method_used'] = 'AI OCR';
        $result['score'] = $scoreResult['score'];
        $result['data'] = $parsedData;
        $result['warnings'] = $scoreResult['warnings'];

        if ($scoreResult['score'] >= 70) {
            $result['recommendation'] = 'LOLOS';
        } elseif ($scoreResult['score'] >= 40) {
            $result['recommendation'] = 'PERLU DICEK MANUAL';
        } else {
            $result['recommendation'] = 'DITOLAK';
        }

        return $result;
    }

    public function generateQRCode(string $data, ?string $filename = null): ?string {
        try {
            $options = new QROptions([
                'version' => 5,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 10,
                'imageBase64' => false
            ]);

            $qrCode = new QRCode($options);

            if (!$filename) {
                $filename = 'qr_' . time() . '_' . uniqid() . '.png';
            }

            $outputPath = __DIR__ . '/../../uploads/qrcodes/' . $filename;

            if (!file_exists(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0777, true);
            }

            $qrCode->render($data, $outputPath);

            return $filename;
        } catch (Exception $e) {
            return null;
        }
    }
}
