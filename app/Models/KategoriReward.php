<?php
namespace App\Models;

use App\Core\Model;

class KategoriReward extends Model {
    protected string $table = 'kategori_reward';

    public function findMatchingReward(string $tingkat, string $kategori, string $raihan): ?int {
        $res = $this->query(
            "SELECT nominal FROM kategori_reward WHERE tingkat = :tingkat AND kategori = :kategori AND raihan = :raihan LIMIT 1",
            ['tingkat' => $tingkat, 'kategori' => $kategori, 'raihan' => $raihan]
        );
        return isset($res[0]['nominal']) ? (int)$res[0]['nominal'] : 0;
    }
}
