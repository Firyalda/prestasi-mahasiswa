<?php
namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected string $table = 'users';

    public function findByUsername(string $username): ?array {
        return $this->findWhere('username', $username);
    }

    public function authenticate(string $username, string $password): ?array {
        $user = $this->findByUsername($username);
        if (!$user) {
            return null;
        }

        // Support both MD5 legacy and standard password_hash
        $hashed = md5($password);
        if ($user['password'] === $hashed || password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    public function register(array $data): int {
        if (isset($data['password'])) {
            $data['password'] = md5($data['password']); // Keep compatible MD5 hashing as existing system
        }
        return $this->create($data);
    }
}
