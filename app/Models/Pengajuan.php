<?php
namespace App\Models;

use App\Core\Model;

class Pengajuan extends Model {
    protected string $table = 'pengajuan';

    public function getByUser(int $userId): array {
        return $this->query("SELECT * FROM pengajuan WHERE user_id = :user_id ORDER BY created_at DESC", ['user_id' => $userId]);
    }

    public function getWithUserDetail(int $id): ?array {
        $result = $this->query("SELECT p.*, u.nama_lengkap, u.nim FROM pengajuan p JOIN users u ON p.user_id = u.id WHERE p.id = :id LIMIT 1", ['id' => $id]);
        return $result[0] ?? null;
    }

    public function getAllPending(): array {
        return $this->query("SELECT p.*, u.nama_lengkap, u.nim FROM pengajuan p JOIN users u ON p.user_id = u.id WHERE p.status = 'diproses' ORDER BY p.created_at DESC");
    }

    public function getAllWithUser(): array {
        return $this->query("SELECT p.*, u.nama_lengkap, u.nim FROM pengajuan p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
    }

    public function getApprovedCount(): int {
        $res = $this->query("SELECT COUNT(*) as total FROM pengajuan WHERE status = 'disetujui'");
        return (int) ($res[0]['total'] ?? 0);
    }

    public function getPendingCount(): int {
        $res = $this->query("SELECT COUNT(*) as total FROM pengajuan WHERE status = 'diproses'");
        return (int) ($res[0]['total'] ?? 0);
    }

    public function updateStatus(int $id, string $status, int $rewardNominal = 0): bool {
        return $this->execute(
            "UPDATE pengajuan SET status = :status, reward_nominal = :reward WHERE id = :id",
            ['status' => $status, 'reward' => $rewardNominal, 'id' => $id]
        );
    }
}
