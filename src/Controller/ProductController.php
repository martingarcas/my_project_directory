<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ProductController extends AbstractController
{
    #[Route('/product', name: 'create_product')]
    public function createProduct(EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(1999);
        $product->setDescription('Ergonomic and stylish!');
        $product->setPrueba(true);  // Asignamos un valor incorrecto para "prueba"

        // Validamos el producto
        $errors = $validator->validate($product);

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            // Pasamos los errores a la vista
            return $this->render('product/index.html.twig', [
                'errors' => $errors
            ]);
        }

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        // Si todo va bien, pasamos el producto creado a la vista
        return $this->render('product/index.html.twig', [
            'product' => $product
        ]);
    }
}
