<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Order;
use App\Entity\Products;
use App\Repository\OrderRepository;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    // recover all orders of the current user 
    #[Route('/', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine, Request $request, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $doctrine->getManager();

        $user= $this->getUser();
        //$current_user = $user->getId();

        $cart = $em->getRepository(Cart::class)->find($user);

        $order = $em->getRepository(Order::class)->findAll();

        if ($order) {
            return $this->json("Order not found", 404);
        }

        $totalPrice = 0;
        $products = $order->getProducts();
        $totalPrice = $order->totalPrice($products);

        $res = [
            'id'=> $order->getId(),
            'totalPrice'=> $totalPrice,
            'creationDate'=> $order->getCreationDate(),
            'products'=> $order->getProducts()
        ];

        return $this->json($res);
    }

    // Get information about a specific order
    #[Route('/{orderId}', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, Request $request, Security $security, int $orderId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $doctrine->getManager();

        $user= $this->getUser();
        //$current_user = $user->getId();

        $cart = $em->getRepository(Cart::class)->find($user);

        $order = $em->getRepository(Order::class)->find($orderId);

        if ($order) {
            return $this->json("Order not found", 404);
        }

        //$order = $em->getRepository(Order::class)->find($id);

        $totalPrice = 0;
        $products = $order->getProducts();
        $totalPrice = $order->totalPrice($products);

        $res = [
            'id'=> $order->getId(),
            'totalPrice'=> $totalPrice,
            'creationDate'=> $order->getCreationDate(),
            'products'=> $order->getProducts()
        ];

        return $this->json($res);
    }
}
