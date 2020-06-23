<?php
/**
 * Post controller.
 */

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostController.
 */
class PostController extends AbstractController
{
    /**
     * Edit action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request        HTTP request
     * @param \App\Entity\Post                          $post           Post entity
     * @param \App\Repository\PostRepository            $postRepository Post repository
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/post/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="edit_post",
     * )
     */
    public function edit(Request $request, Post $post, PostRepository $postRepository): Response
    {
        $logged = $this->getUser();
        if ($logged !== $post->getUser()) {
            $this->addFlash('danger', 'operation_not_permitted');

            return $this->redirectToRoute('profile', ['id' => $post->getUser()->getId()]);
        }
        $form = $this->createForm(PostType::class, $post, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postRepository->save($post);
            $this->addFlash('success', 'message_updated_successfully');

            return $this->redirectToRoute('profile', ['id' => $post->getUser()->getId()]);
        }

        return $this->render(
            'post/edit.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request        HTTP request
     * @param \App\Entity\Post                          $post           Post entity
     * @param \App\Repository\PostRepository            $postRepository Post repository
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/post/{id}/delete",
     *     methods={"GET", "DELETE"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="delete_post",
     * )
     */
    public function delete(Request $request, Post $post, PostRepository $postRepository): Response
    {
        $logged = $this->getUser();
        if ($logged !== $post->getUser()) {
            $this->addFlash('danger', 'operation_not_permitted');

            return $this->redirectToRoute('profile', ['id' => $post->getUser()->getId()]);
        }
        $form = $this->createForm(FormType::class, $post, ['method' => 'DELETE']);
        $form->handleRequest($request);

        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $postRepository->delete($post);
            $this->addFlash('success', 'message_deleted_successfully');

            return $this->redirectToRoute('profile', ['id' => $post->getUser()->getId()]);
        }

        return $this->render(
            'post/delete.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }
}
