<?php
/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\FriendRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    /**
     * Profile action.
     *
     * @param Request            $request          HTTP request
     * @param User               $user             User
     * @param PostRepository     $postRepository   Post Repository
     * @param FriendRepository   $friendRepository Friend Repository
     * @param PaginatorInterface $paginator        Paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/profile",
     *     methods={"GET", "POST"},
     *     name="profile",
     *     requirements={"id": "[1-9]\d*"},
     * )
     */
    public function profile(Request $request, User $user, PostRepository $postRepository, FriendRepository $friendRepository, PaginatorInterface $paginator): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $logged = $this->getUser();
        $pagination = $paginator->paginate(
            $postRepository->userPosts($user),
            $request->query->getInt('page', 1),
            Post::PAGINATOR_ITEMS_PER_PAGE
        );

        if ($form->isSubmitted() && $form->isValid()) {
            if ($logged === $user) {
                $this->addFlash('danger', 'message_created_failed');

                return $this->redirectToRoute('profile', ['id' => $user->getId()]);
            }
            $post->setUser($user);
            $postRepository->save($post);
            $this->addFlash('success', 'message_created_successfully');

            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }
        $connection = $logged === $user ? null : $friendRepository->getFriendConnection($user, $logged);
        $friendsNumber = count($friendRepository->getFriends($user)->getQuery()->getResult());

        return $this->render(
            'profile.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                'connection' => $connection,
                'pagination' => $pagination,
                'friendsNumber' => $friendsNumber,
            ]
        );
    }
}
