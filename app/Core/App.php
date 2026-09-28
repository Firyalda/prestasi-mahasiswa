<?php
namespace App\Core;

class App {
    public static App $app;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;

    public function __construct() {
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->router = new Router($this->request, $this->response);
    }

    public function run(): void {
        $this->router->resolve();
    }
}
