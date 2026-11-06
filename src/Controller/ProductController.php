<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\AddProductHistoryTypeForm;
use App\Form\ProductType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product')]
#[IsGranted('ROLE_EDITOR')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('image_dir'),
                        $newFileName
                    );
                } catch (FileException $exception) {

                }

                $product->setImage($newFileName);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            // Ajout du stock initial lors de la création du produit
            $stockHistory = new AddProductHistory();
            $stockHistory->setQte($product->getStock() ?? 0);
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($stockHistory);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été ajouté');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('image_dir'),
                        $newFileName
                    );

                    $oldImage = $product->getImage();
                    if ($oldImage) {
                        $oldImagePath = $this->getParameter('image_dir') . '/' . $oldImage;
                        if (file_exists($oldImagePath)) {
                            @unlink($oldImagePath);
                        }
                    }
                } catch (FileException $exception) {

                }

                $product->setImage($newFileName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été modifié');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/add/product/{id}/stock', name: 'app_product_stock_add', methods: ['GET', 'POST'])]
    public function addStock(int $id, EntityManagerInterface $entityManager, Request $request, ProductRepository $productRepository): Response
    {
        $addStock = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryTypeForm::class, $addStock);
        $form->handleRequest($request);

        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($addStock->getQte() > 0) {
                $newQte = ($product->getStock() ?? 0) + $addStock->getQte();
                $product->setStock($newQte);

                $addStock->setCreatedAt(new \DateTimeImmutable());
                $addStock->setProduct($product);

                $entityManager->persist($addStock);
                $entityManager->flush();

                $this->addFlash('success', 'Le stock du produit a été modifié !');

                return $this->redirectToRoute('app_product_index');
            } else {
                $this->addFlash('danger', 'Impossible de mettre à jour le stock ! La quantité doit être > 0');

                return $this->redirectToRoute('app_product_stock_add', ['id' => $product->getId()]);
            }
        }

        return $this->render('product/addStock.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/add/product/{id}/stock/history', name: 'app_product_stock_add_history', methods: ['GET'])]
    public function productAddHistory(int $id, ProductRepository $productRepository, AddProductHistoryRepository $addProductHistoryRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $productAddedHistory = $addProductHistoryRepository->findBy(
            ['product' => $product],
            ['id' => 'DESC']
        );

        return $this->render('product/addedStockHistoryShow.html.twig', [
            'productsAdded' => $productAddedHistory,
            'product' => $product,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $image = $product->getImage();
            if ($image) {
                $imagePath = $this->getParameter('image_dir') . '/' . $image;
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été supprimé');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
