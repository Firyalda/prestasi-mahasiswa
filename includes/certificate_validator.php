<?php
// includes/certificate_validator.php - OOP Service Wrapper for Backward Compatibility

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\CertificateValidatorService;

class CertificateValidator {
    private CertificateValidatorService $service;

    public function __construct($db_connection = null) {
        $this->service = new CertificateValidatorService();
    }

    public function extractQRCode($imagePath) {
        return $this->service->extractQRCode($imagePath);
    }

    public function verifyViaQRCode($qrText) {
        return $this->service->verifyViaQRCode($qrText);
    }

    public function extractTextWithOCR($imagePath) {
        return $this->service->extractTextWithOCR($imagePath);
    }

    public function parseCertificateData($text) {
        return $this->service->parseCertificateData($text);
    }

    public function calculateAuthenticityScore($text, $parsedData) {
        return $this->service->calculateAuthenticityScore($text, $parsedData);
    }

    public function validateCertificate($filePath, $pengajuanId = null) {
        return $this->service->validateCertificate($filePath, $pengajuanId ?? 0);
    }

    public function generateQRCode($data, $filename = null) {
        return $this->service->generateQRCode($data, $filename);
    }
}
?>