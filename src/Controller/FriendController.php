<?php
/**
 * Friend controller.
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
 * Class FriendController.
 */
class FriendController extends AbstractController
{
    /**
     * Search action.
     *
     * @param Request          $request          HTTP request
     * @param User             $user             User
     * @param FriendRepository $friendRepository Repository
     *
     * @return Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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
        if (null === $currentUser) {
            return $this->redirectToRoute('app_login');
        }

        $connection = $friendRepository->getFriendConnection($currentUser, $user);
        if (null !== $connection || $user === $currentUser) {
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
     * @param Request          $request          HTTP request
     * @param string           $type             Type
     * @param User             $user             User
     * @param FriendRepository $friendRepository Repository
     *
     * @return Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{type}/friend/{id}",
     *     methods={"GET"},
     *     name="manage_friend",
     * )
     */
    public function acceptFriend(Request $request, $type, User $user, FriendRepository $friendRepository): Response
    {
        if ('accept' !== $type && 'decline' !== $type) {
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }

        $currentUser = $this->getUser();
        if (null === $currentUser) {
            return $this->redirectToRoute('app_login');
        }

        $invitation = $friendRepository->getInvitation($user, $currentUser); //musi przyjść od kogoś do zalogowanego
        if (null === $invitation || $invitation->getAccepted() === true) {
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }

        if ('accept' === $type) {
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
     * @param Request            $request          HTTP request
     * @param User               $user             User
     * @param FriendRepository   $friendRepository Repository
     * @param PaginatorInterface $paginator        Paginator
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
                'friendRepository' => $friendRepository,
            ]
        );
    }

    /**
     * Search action.
     *
     * @param Request            $request          HTTP request
     * @param FriendRepository   $friendRepository Repository
     * @param PaginatorInterface $paginator        Paginator
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
                'pagination' => $pagination,
            ]
        );
    }
}
