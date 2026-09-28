<?php
namespace App\Core;

class Response {
    public function setStatusCode(int $code): self {
        http_response_code($code);
        return $this;
    }

    public function redirect(string $url): void {
        header("Location: " . $url);
        exit();
    }

    public function json(array $data, int $statusCode = 200): void {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }
}
