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
use App\Repository\ProductsRepository;

#[Route('/api')]
class ProductsController extends AbstractController
{
    // get list of products
    #[Route('/products', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $products = $em->getRepository(Products::class)->findAll();

        if (!$products) {
            return $this->json("No product found for id " .$productId, 404);
        }

        return $this->json($products, 200);
        /*$data_products = [];

        foreach ($products as $product) {
            $data_products = 
            [
                'id'=>$product->getId(),
                'name'=>$product->getName(),
                'description'=>$product->getDescription(),
                'photo'=>$product->getPhoto(),
                'price'=>$product->getPrice()
            ];
        }

        return $this->json($data_products);*/

    }

    // get list of specific product
    #[Route('/products/{productId}', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $productId): Response
    {
        $product = $doctrine->getRepository(Products::class)->find($productId);

        if (!$product) {
            return $this->json("No product found for id " .$productId, 404);
        }

        $data_product = 
        [
            'id'=>$product->getId(),
            'name'=>$product->getName(),
            'description'=>$product->getDescription(),
            'photo'=>$product->getPhoto(),
            'price'=>$product->getPrice()
        ];

        return $this->json($data_product);

    }

    // add a new product
    #[Route('/products', methods: ['POST'])]
    public function new(ManagerRegistry $doctrine, Request $request, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $doctrine->getManager();
        $decoded = json_decode($request->getContent());

        $name = $decoded->name;
        $description = $decoded->description;
        $photo = $decoded->photo;
        $price = $decoded->price;

        $product = new Products();
        $product->setName($name);
        $product->setDescription($description);
        $product->setPhoto($photo);
        $product->setPrice($price);

        $em->persist($product);
        $em->flush();

        return $this->json('Created new product successfully with id ' .$product->getId(), 200);

    }

    // update a product
    #[Route('/products/{productId}', methods: ['PUT'])]
    public function edit(ManagerRegistry $doctrine, Request $request, int $productId, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $doctrine->getManager();
        $product = $em->getRepository(Products::class)->find($productId);

        if (!$product) {
            return $this->json("No product found for id " .$productId, 404);
        }

        $decoded = json_decode($request->getContent());

        $name = $decoded->name;
        $description = $decoded->description;
        $photo = $decoded->photo;
        $price = $decoded->price;

        $product->setName($name);
        $product->setDescription($description);
        $product->setPhoto($photo);
        $product->setPrice($price);

        $em->flush();

        $data_product = 
        [
            'id'=>$product->getId(),
            'name'=>$product->getName(),
            'description'=>$product->getDescription(),
            'photo'=>$product->getPhoto(),
            'price'=>$product->getPrice()
        ];

        return $this->json($data_product);

    }

    // delete a product
    #[Route('/products/{productId}', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $productId, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $doctrine->getManager();
        $product = $em->getRepository(Products::class)->find($productId);

        if (!$product) {
            return $this->json("No product found for id " .$productId, 404);
        }

        $em->remove($product);
        $em->flush();

        return $this->json("Deleted a product successfully with id " .$productId);

    }
}
