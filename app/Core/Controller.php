<?php
namespace App\Core;

abstract class Controller {
    protected Request $request;
    protected Response $response;
    protected Session $session;

    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
    }

    public function render(string $view, array $params = [], string $layout = ''): void {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        // Global variables available in views
        $conn = Database::getInstance();
        $session = $this->session;

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            $viewPath = __DIR__ . '/../../' . $view . '.php';
        }

        if (!file_exists($viewPath)) {
            die("View '{$view}' not found at path: {$viewPath}");
        }

        if (!empty($layout)) {
            ob_start();
            include $viewPath;
            $content = ob_get_clean();

            $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content;
            }
        } else {
            include $viewPath;
        }
    }

    public function redirect(string $url): void {
        $this->response->redirect($url);
    }

    public function json(array $data, int $statusCode = 200): void {
        $this->response->json($data, $statusCode);
    }
}
