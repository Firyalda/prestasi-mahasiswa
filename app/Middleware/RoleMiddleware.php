<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Session;
use App\Core\Response;

class RoleMiddleware implements Middleware {
    private array $allowedRoles;

    public function __construct(array $roles) {
        $this->allowedRoles = $roles;
    }

    public function execute(): void {
        $session = new Session();
        $userRole = $session->get('role', 'mahasiswa');

        if (!in_array($userRole, $this->allowedRoles)) {
            $response = new Response();
            $redirectMap = [
                'admin'      => '/admin/dashboard.php',
                'reviewer'   => '/verifikator/dashboard.php',
                'superadmin' => '/pimpinan/dashboard.php',
                'mahasiswa'  => '/mahasiswa/dashboard.php',
            ];
            $target = $redirectMap[$userRole] ?? '/login.php';
            $response->redirect($target);
        }
    }
}
