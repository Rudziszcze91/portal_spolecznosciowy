<?php
/**
 * Post controller.
 */

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Service\PostService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * Category service.
     *
     * @var \App\Service\PostService
     */
    private $postService;

    /**
     * CategoryController constructor.
     *
     * @param \App\Service\PostService $postService Post service
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Edit action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     * @param \App\Entity\Post                          $post    Post entity
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
     *
     * @IsGranted(
     *     "EDIT",
     *     subject="post",
     * )
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);
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
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     * @param \App\Entity\Post                          $post    Post entity
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
     *
     * @IsGranted(
     *     "DELETE",
     *     subject="post",
     * )
     */
    public function delete(Request $request, Post $post): Response
    {
        $form = $this->createForm(FormType::class, $post, ['method' => 'DELETE']);
        $form->handleRequest($request);

        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->delete($post);
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
