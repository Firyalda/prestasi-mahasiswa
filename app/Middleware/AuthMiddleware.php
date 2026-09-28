<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Session;
use App\Core\Response;

class AuthMiddleware implements Middleware {
    public function execute(): void {
        $session = new Session();
        if (!$session->has('user_id') || empty($session->get('user_id'))) {
            $response = new Response();
            $response->redirect('/login.php');
        }
    }
}
