<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class OrderController extends AbstractController
{

    /**
     * @Route("/order", name="app_order")
     */
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    //get all orders
    /**
     * @Route("/api/orders", name="app_orders", methods={"GET"})
     */
    public function getOrders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();

        $ordersArray = [];

        foreach ($orders as $order) {
            $ordersArray[] = [
                'id' => $order->getId(),
                'user' => $order->getUser(),
                'totalPrice' => $order->getTotalPrice(),
                'creationDate' => $order->getCreationDate(),
                'isActive' => $order->getIsActive(),
            ];
        }

        return new JsonResponse($ordersArray, Response::HTTP_OK);
    }

    /**
     * @Route("/api/orders/user", name="app_orders_user", methods={"GET"})
     */
    public function getOrdersUser(
        Security $security,
        OrderRepository $orderRepository
    ): Response{
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $orders = $orderRepository->findBy(['user' => $user]);

        $ordersArray = [];

        foreach ($orders as $order) {
            $productsArray = [];

            // Convertir les entités de produits en tableaux associatifs
            foreach ($order->getProducts() as $product) {
                $productsArray[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'available' => $product->getAvailable(),
                ];
            }

            $ordersArray[] = [
                'id' => $order->getId(),
                'user' => $order->getUser(),
                'totalPrice' => $order->getTotalPrice(),
                'creationDate' => $order->getCreationDate(),
                'isActive' => $order->getIsActive(),

                // Ajouter le tableau de produits
                'products' => $productsArray,
            ];
        }

        return new JsonResponse($ordersArray, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/api/order", name="app_order", methods={"GET"})
     */
    public function getOrder(
        Security $security,
        OrderRepository $orderRepository
    ): Response{
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Récupérer l'Order actif de l'utilisateur
        $activeOrder = $orderRepository->findOneBy(['user' => $user, 'isActive' => true]);

        if (!$activeOrder) {
            return new JsonResponse(['error' => 'No active order found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $products = $activeOrder->getProducts();

        $productsArray = [];

        foreach ($products as $product) {
            $productsArray[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'photo' => $product->getPhoto(),
                'price' => $product->getPrice(),
            ];
        }

        return new JsonResponse($productsArray, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/api/order/addProduct/{productId}", name="app_add_product_to_order", methods={"POST"})
     */
    public function addProductToOrder(
        int $productId,
        Security $security,
        EntityManagerInterface $entityManager,
        ProductsRepository $productsRepository,
        OrderRepository $orderRepository
    ): Response{
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $product = $productsRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$product->getAvailable()) {
            return new JsonResponse(['error' => 'Product is not available'], JsonResponse::HTTP_CONFLICT);
        }

        // Récupérer l'Order actif de l'utilisateur
        $activeOrder = $orderRepository->findOneBy(['user' => $user, 'isActive' => true]);

        // Si aucun Order actif n'est trouvé, créez-en un et définissez-le comme actif
        if (!$activeOrder) {
            $activeOrder = new Order();
            $activeOrder->setUser($user);
            $activeOrder->setTotalPrice(0);
            $activeOrder->setCreationDate(new \DateTimeImmutable());
            $activeOrder->setisActive(true);
        }

        $activeOrder->setTotalPrice($activeOrder->getTotalPrice() + $product->getPrice());

        $activeOrder->addProduct($product);
        $product->setAvailable(false);

        $entityManager->persist($activeOrder);
        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Product added to order'], JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/api/order/removeProduct/{productId}", name="app_remove_product_from_order", methods={"POST"})
     */
    public function removeProductFromOrder(
        int $productId,
        Security $security,
        EntityManagerInterface $entityManager,
        ProductsRepository $productsRepository,
        OrderRepository $orderRepository
    ): Response{
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $product = $productsRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Récupérer l'Order actif de l'utilisateur
        $activeOrder = $orderRepository->findOneBy(['user' => $user, 'isActive' => true]);

        // Si aucun Order actif n'est trouvé, créez-en un et définissez-le comme actif
        if (!$activeOrder) {
            return new JsonResponse(['error' => 'No active order found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $activeOrder->removeProduct($product);
        $product->setAvailable(true);

        $entityManager->persist($activeOrder);
        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Product removed from order'], JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/api/order/validate", name="app_validate_order", methods={"POST"})
     */
    public function validateOrder(
        Security $security,
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository
    ): Response{
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Récupérer l'Order actif de l'utilisateur
        $activeOrder = $orderRepository->findOneBy(['user' => $user, 'isActive' => true]);

        // Si aucun Order actif n'est trouvé, créez-en un et définissez-le comme actif
        if (!$activeOrder) {
            return new JsonResponse(['error' => 'No active order found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $activeOrder->setIsActive(false);

        $newOrder = new Order();
        $newOrder->setUser($user);
        $newOrder->setTotalPrice(0);
        $newOrder->setCreationDate(new \DateTimeImmutable());
        $newOrder->setisActive(true);

        $entityManager->persist($activeOrder);
        $entityManager->persist($newOrder);

        $entityManager->flush();

        return new JsonResponse(['success' => 'Order validated'], JsonResponse::HTTP_OK);
    }

}
