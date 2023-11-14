<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Products;
use App\Entity\Users;
use App\Entity\Cart;
use App\Entity\Order;
use App\Repository\CartRepository;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/carts')]
class CartController extends AbstractController
{
    // add a product to the shopping cart
    #[Route('/{productId}', methods: ['POST'])]
    public function add(ManagerRegistry $doctrine, Request $request, Security $security, int $productId, SerializerInterface $serializer): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $doctrine->getManager();

        $products = $em->getRepository(Products::class)->find($productId);
        //$jsonproducts = $serializer->serialize($products, 'json');
        $user= $this->getUser();

        //$cart = $em->getRepository(Cart::class)->find($user);
        $cart = new Cart();

        $cart->setUser($user);
        $em->persist($cart);

        if (!$products) {   
            return $this->json('Product not found',404);
        }

        $cart->addProduct($products);
        //$cart->setUser($user);
        $em->persist($cart);
        $em->flush();

        return $this->json($cart);
    }

    // remove a product to the shopping cart
    #[Route('/{productId}', methods: ['DELETE'])]
    public function remove(ManagerRegistry $doctrine, Request $request, Security $security, int $productId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $doctrine->getManager();

        $products = $em->getRepository(Products::class)->find($productId);
        $user = $this->getUser();

        $cart = $em->getRepository(Cart::class)->find($user);
        //return $this->json($products);

        if (!$products) {
            return $this->json('Product not found', 404);
        }

        if (!$cart) {
            return $this->json('Cart not found', 404);
        }

        $cart->removeProduct($products);
        $em->flush();

        return $this->json('Deleted successfully', 202);
    }

    //list of products in the cart
    #[Route('', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, Request $request, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $doctrine->getManager();

        $user= $this->getUser();
        $current_user = $user->getId();

        $cart = $em->getRepository(Cart::class)->find($user);

        if (!$cart) {
            return $this->json('Cart not found', 404);
        }

        $products = $cart->getProducts();

        return $this->json($cart);
    }

    //Validation of the cart
    #[Route('/validate', methods: ['PUT'])]
    public function validate(ManagerRegistry $doctrine, Request $request, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $doctrine->getManager();

        $user= $this->getUser();

        $cart = $em->getRepository(Cart::class)->find($user);

        if (!$cart) {
            return $this->json('Cart not found', 404);
        }

        $products = $cart->getProducts();

        $order = new Order();

        //$products = $cart->getProducts();

        foreach ($products as $product) {
            $order->addProduct($product);
        }

        $order->setUser($user);

        $em->persist($order);
        $em->flush();

        $em->remove($cart);
        $em->flush();

        return $this->json($cart);
    }
}
