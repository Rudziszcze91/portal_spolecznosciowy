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
        if ($connection !== null || $user === $currentUser)  {
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
     *     "/{type}/friend/{id}",
     *     methods={"GET"},
     *     name="manage_friend",
     * )
     */
    public function acceptFriend(Request $request, $type, User $user, FriendRepository $friendRepository): Response
    {
        if ($type !== 'accept' && $type !== 'decline') {
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }

        $currentUser = $this->getUser();
        if ($currentUser === null) {
            return $this->redirectToRoute('app_login');
        }

        $invitation = $friendRepository->getInvitation($user, $currentUser); //musi przyjść od kogoś do zalogowanego
        if ($invitation === null || $invitation->getAccepted() === true)  {
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }

        if ($type === 'accept') {
            $invitation->setAccepted(true);
            $friendRepository->save($invitation);
            $this->addFlash('success', 'message_accept_friend_success');
        } else {
            $friendRepository->delete($invitation);
            $this->addFlash('success', 'message_decline_friend_success');
        }


        return $this->redirectToRoute('profile', ['id' => $user->getId()]);
    }

    /**
     * Search action.
     *
     * @param Request $request HTTP request
     * @param FriendRepository $friendRepository HTTP request
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
    public function friends(Request $request, User $user, FriendRepository $friendRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $friendRepository->getFriends($user),
            $request->query->getInt('page', 1),
            User::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'friends.html.twig',
            [
                'pagination' => $pagination,
                'user' => $user,
                'friendRepository' => $friendRepository
            ]
        );
    }

    /**
     * Search action.
     *
     * @param Request $request HTTP request
     * @param FriendRepository $friendRepository HTTP request
     * @param PaginatorInterface $paginator Paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/friend/requests",
     *     methods={"GET"},
     *     name="friend_requests",
     * )
     */
    public function friendRequests(Request $request, FriendRepository $friendRepository, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $pagination = $paginator->paginate(
            $friendRepository->getInvitations($user),
            $request->query->getInt('page', 1),
            User::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'requests.html.twig',
            [
                'pagination' => $pagination
            ]
        );
    }
}
