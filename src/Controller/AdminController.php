<?php
// src/Controller/AdminController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/preguntas', name: 'admin_preguntas')]
    public function preguntas(): Response
    {
        return $this->render('admin/preguntas.html.twig');
    }
}
