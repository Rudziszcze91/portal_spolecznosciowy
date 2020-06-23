<?php
/**
 * Web controller.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WebController.
 *
 */
class WebController extends AbstractController
{
    /**
     * Index action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="index",
     * )
     */
    public function index(Request $request): Response
    {
        return $this->render(
            'index.html.twig'
        );
    }

    /**
     * Search action.
     *
     * @param Request            $request          HTTP request
     * @param UserRepository     $userRepository   Repository
     * @param FriendRepository   $friendRepository Repository
     * @param PaginatorInterface $paginator        Paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/search",
     *     methods={"GET"},
     *     name="search",
     * )
     */
    public function search(Request $request, UserRepository $userRepository, FriendRepository $friendRepository, PaginatorInterface $paginator): Response
    {
        $phrase = $request->query->get('phrase');
        $pagination = $paginator->paginate(
            $userRepository->searchUsers($phrase),
            $request->query->getInt('page', 1),
            User::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'search.html.twig',
            [
                'pagination' => $pagination,
                'friendRepository' => $friendRepository,
                'phrase' => $phrase,
            ]
        );
    }

    /**
     * add comment action.
     *
     * @param Request           $request           HTTP request
     * @param Post              $post              Post
     * @param CommentRepository $commentRepository Post Repository
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/comment/add",
     *     methods={"GET", "POST"},
     *     name="add_comment",
     *     requirements={"id": "[1-9]\d*"},
     * )
     */
    public function addComment(Request $request, Post $post, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $logged = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($logged);
            $comment->setPost($post);
            $commentRepository->save($comment);
            $this->addFlash('success', 'message_created_successfully');

            return $this->redirectToRoute('profile', ['id' => $post->getUser()->getId()]);
        }

        return $this->render(
            'comment/add.html.twig',
            [
                'post' => $post,
                'form' => $form->createView(),
            ]
        );
    }
}
