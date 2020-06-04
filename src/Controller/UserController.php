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
 *
 */
class UserController extends AbstractController
{
    /**
     * Profile action.
     *
     * @param Request $request HTTP request
     * @param User $user User
     * @param PostRepository $postRepository Post Repository
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @Route(
     *     "/{id}/profile",
     *     methods={"GET", "POST"},
     *     name="profile",
     *     requirements={"id": "[1-9]\d*"},
     * )
     */
    public function profile(Request $request, User $user, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $logged = $this->getUser();
        $pagination = $paginator->paginate(
            $postRepository->queryAll(),
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

        return $this->render(
            'profile.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * Search action.
     *
     * @param Request $request HTTP request
     * @param UserRepository $userRepository HTTP request
     * @param PaginatorInterface $paginator Paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/search",
     *     methods={"GET"},
     *     name="search",
     * )
     */
    public function search(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
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
                'phrase' => $phrase,
            ]
        );
    }

    /**
     * Search action.
     *
     * @param Request $request HTTP request
     * @param UserRepository $userRepository HTTP request
     * @param PaginatorInterface $paginator Paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/add/friend/{id}",
     *     methods={"GET"},
     *     name="add_friend",
     * )
     */
    public function addFriend(Request $request, User $user, FriendRepository $friendRepository): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser === null) {
            return $this->redirectToRoute('app_login');
        }

        $connection = $friendRepository->getFriendConnection($currentUser, $user);
        if ($connection !== null)  {
            $this->addFlash('danger', 'message_add_friend_failed');

            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }

        $connection = new Friend();
        $connection->setAccepted(false);
        $connection->setFromUser($currentUser);
        $connection->setToUser($user);
        $friendRepository->save($connection);

        $this->addFlash('success', 'message_add_friend_success');

        return $this->redirectToRoute('profile', ['id' => $user->getId()]);
    }

    /**
     * Search action.
     *
     * @param Request $request HTTP request
     * @param UserRepository $userRepository HTTP request
     * @param PaginatorInterface $paginator Paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/accept/friend/{id}",
     *     methods={"GET"},
     *     name="accept_friend",
     * )
     */
    public function acceptFriend(Request $request, User $user, FriendRepository $friendRepository): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser === null) {
            return $this->redirectToRoute('app_login');
        }

        $invitation = $friendRepository->getInvitation($user, $currentUser); //musi przyjść od kogoś do zalogowanego
        if ($invitation === null || $invitation->getAccepted() === true)  {
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }

        $invitation->setAccepted(true);
        $friendRepository->save($invitation);
        $this->addFlash('success', 'message_accept_friend_success');

        return $this->redirectToRoute('profile', ['id' => $user->getId()]);
    }

    /**
     * Search action.
     *
     * @param Request $request HTTP request
     * @param UserRepository $userRepository HTTP request
     * @param PaginatorInterface $paginator Paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/{id}/friends",
     *     methods={"GET"},
     *     name="friends",
     * )
     */
    public function friends(Request $request, User $user, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $phrase = $request->query->get('phrase');
        $pagination = $paginator->paginate(
            $userRepository->getFriends($user),
            $request->query->getInt('page', 1),
            User::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'search.html.twig',
            [
                'pagination' => $pagination,
                'phrase' => $user->getId(),
            ]
        );
    }
}
