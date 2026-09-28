<?php
namespace App\Core;

class Session {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public function destroy(): void {
        session_destroy();
        $_SESSION = [];
    }

    public function setFlash(string $type, string $title, string $text): void {
        $_SESSION['flash_message'] = [
            'type'  => $type,
            'title' => $title,
            'text'  => $text,
        ];
    }

    public function getFlash(): ?array {
        $flash = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        return $flash;
    }
}
