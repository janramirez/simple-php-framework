<?php

namespace Controller;

defined('ROOTPATH') OR exit('Access denied!');


/**
 * Home class
 * 
 */
class Home {

    use MainController;

    public function index() {

        $data['username'] = empty($_SESSION['USER']) ? 'Guest' : $_SESSION['USER']->email;
        $this->view('home', $data);
    }

}
