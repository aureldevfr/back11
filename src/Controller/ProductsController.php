<?php

// src/Controller/ProductsController.php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ProductsController extends AbstractController
{

    /**
     * @Route("/products", name="products_index", methods={"GET"})
     */
    public function index(ProductsRepository $productsRepository): Response
    {
        // Récupérer la liste des produits et retourner la réponse

        $products = $productsRepository->findAll();

        $productsArray = [];

        foreach ($products as $product) {
            $productsArray[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'photo' => $product->getPhoto(),
                'price' => $product->getPrice(),
                'category' => $product->getCategory(),
                'available' => $product->getAvailable() ? true : false,
            ];
        }

        return new JsonResponse($productsArray, Response::HTTP_OK);
    }

    /**
     * @Route("/products/create", name="products_create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer un nouveau produit et retourner la réponse

        $data = json_decode($request->getContent(), true);

        $product = new Products();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPhoto($data['photo']);
        $product->setPrice($data['price']);
        $product->setCategory($data['category']);
        $product->setAvailable(true);

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Product created'], Response::HTTP_CREATED);

    }

    /**
     * @Route("/products/{id}", name="products_show", methods={"GET"})
     */
    public function show(Products $product): Response
    {
        // Récupérer un produit spécifique et retourner la réponse

        $productArray = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'photo' => $product->getPhoto(),
            'price' => $product->getPrice(),
            'category' => $product->getCategory()->getName(),
        ];

        return new JsonResponse($productArray, Response::HTTP_OK);

    }

    /**
     * @Route("/api/products/edit/{id}", name="products_edit", methods={"PUT"})
     */
    public function edit(Request $request, Products $product, EntityManagerInterface $entityManager, Security $security): Response
    {

        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Modifier un produit spécifique et retourner la réponse

        $data = json_decode($request->getContent(), true);

        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPhoto($data['photo']);
        $product->setPrice($data['price']);
        $product->setCategory($data['category']);

        $entityManager->flush();

        return new JsonResponse(['status' => 'Product updated'], Response::HTTP_OK);

    }

    /**
     * @Route("/api/products/delete/{id}", name="products_delete", methods={"DELETE"})
     */
    public function delete(Products $product, EntityManagerInterface $entityManager, Security $security): Response
    {

        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Supprimer un produit spécifique et retourner la réponse

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Product deleted'], Response::HTTP_OK);
    }
}
