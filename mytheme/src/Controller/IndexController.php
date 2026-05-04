<?php

namespace App\Controller;

use App\Core\Controller\Wordpress;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Wordpress {

    #[Route('/', name: 'index')]
    public function index() {
        return $this->render('index.html.twig', []);
    }
}