<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category/new', name: 'app_category_new')]
    public function addCategory(EntityManagerInterface $entityManager, Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryTypeForm::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/new.html.twig', ['categoryForm' => $form->createView()]);
    }
    #[Route('/category/{id}/update', name: 'app_category_update')]
public function updateCategory(Category $category, EntityManagerInterface $entityManager,
    Request $request): Response
{

    $form = $this->createForm(CategoryTypeForm::class, $category);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('app_category');
    }
    return $this->render('category/update.html.twig',
            ['categoryForm' => $form->createView()]);
}
}
