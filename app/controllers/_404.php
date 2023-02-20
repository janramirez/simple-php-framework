<?php

namespace Controller;

defined('ROOTPATH') OR exit('Access denied!');
class _404 {
    use MainController;

    public function index() {

        http_response_code(404);

        $this->view('404');
        die();
    }
}
