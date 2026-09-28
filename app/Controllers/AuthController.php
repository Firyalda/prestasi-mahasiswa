<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    private User $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function login(): void {
        // If already logged in, redirect based on role
        if ($this->session->has('user_id') && !empty($this->session->get('user_id'))) {
            $role = $this->session->get('role', 'mahasiswa');
            $redirectMap = [
                'admin'      => 'admin/dashboard.php',
                'reviewer'   => 'verifikator/dashboard.php',
                'superadmin' => 'pimpinan/dashboard.php',
                'mahasiswa'  => 'mahasiswa/dashboard.php'
            ];
            $redirect = $redirectMap[$role] ?? 'mahasiswa/dashboard.php';
            $this->redirect($redirect);
            return;
        }

        $error = '';

        if ($this->request->isPost()) {
            $username = trim($this->request->input('username', ''));
            $password = trim($this->request->input('password', ''));

            $user = $this->userModel->authenticate($username, $password);

            if ($user) {
                $this->session->set('user_id', $user['id']);
                $this->session->set('username', $user['username']);
                $this->session->set('nama', $user['nama_lengkap']);
                $this->session->set('role', $user['role']);
                $this->session->set('nim', $user['nim'] ?? null);
                $this->session->set('login_success', true);
                $this->session->set('login_nama', $user['nama_lengkap']);

                $this->session->setFlash('success', 'Login Berhasil', 'Selamat datang kembali!');

                $roleMap = [
                    'admin'      => 'admin/dashboard.php',
                    'reviewer'   => 'verifikator/dashboard.php',
                    'superadmin' => 'pimpinan/dashboard.php',
                    'mahasiswa'  => 'mahasiswa/dashboard.php'
                ];

                $redirect = $roleMap[$user['role']] ?? 'mahasiswa/dashboard.php';
                $this->redirect($redirect);
                return;
            } else {
                $error = "Username atau password salah!";
            }
        }

        $this->render('login', ['error' => $error]);
    }

    public function logout(): void {
        $this->session->destroy();
        $this->redirect('login.php');
    }
}
