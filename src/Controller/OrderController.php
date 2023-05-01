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

    /**
     * @Route("/api/order/{orderId}/addProduct/{productId}", name="app_add_product_to_order", methods={"POST"})
     */
    public function addProductToOrder(
        int $orderId,
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
        $order = $orderRepository->find($orderId);

        if (!$product || !$order) {
            return new JsonResponse(['error' => 'Product or order not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$product->getAvailable()) {
            return new JsonResponse(['error' => 'Product is not available'], JsonResponse::HTTP_CONFLICT);
        }

        $order->addProduct($product);
        $product->setAvailable(false);

        $entityManager->persist($order);
        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Product added to order'], JsonResponse::HTTP_OK);
    }
}
