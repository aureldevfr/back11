<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    /**
     * @Route("/api/logout", name="app_logout", methods={"POST"})
     */
    public function logout()
    {
        // Le code de cette méthode restera vide car Symfony gère la déconnexion pour vous.
    }
}
