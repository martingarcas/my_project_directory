<?php

// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'create_product')]
    public function createProduct(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $product = new Product(); // Creamos una nueva instancia de Product

        // Creamos el formulario basado en la entidad Product
        $form = $this->createForm(ProductType::class, $product);

        // Procesamos el formulario cuando el usuario lo envía
        $form->handleRequest($request);

        // Si el formulario es enviado y es válido
        if ($form->isSubmitted() && $form->isValid()) {
            // Validamos el producto
            $errors = $validator->validate($product);
            
            if (count($errors) > 0) {
                // Pasamos los errores a la vista si no es válido
                return $this->render('product/index.html.twig', [
                    'errors' => $errors,
                    'form' => $form->createView(),
                ]);
            }

            // Persistimos el producto en la base de datos
            $entityManager->persist($product);
            $entityManager->flush();

            // Redirigimos a la página de éxito o mostramos el producto
            return $this->render('product/index.html.twig', [
                'product' => $product,
                'form' => $form->createView(),
            ]);
        }

        // Si el formulario no fue enviado o no es válido, lo mostramos
        return $this->render('product/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

