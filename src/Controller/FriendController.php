<?php
/**
 * Friend controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\FriendRepository;
use App\Service\FriendService;
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
     * Category service.
     *
     * @var \App\Service\FriendService
     */
    private $friendService;

    /**
     * CategoryController constructor.
     *
     * @param \App\Service\FriendService $friendService Friend service
     */
    public function __construct(FriendService $friendService)
    {
        $this->friendService = $friendService;
    }

    /**
     * Add friend action.
     *
     * @param User $user User
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
    public function addFriend(User $user): Response
    {
        $currentUser = $this->getUser();
        $connection = $this->friendService->getFriendConnection($currentUser, $user);
        if (null !== $connection || $user === $currentUser) {
            $this->addFlash('danger', 'message_add_friend_failed');

            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }
        $this->friendService->addFriendConnection($currentUser, $user);

        $this->addFlash('success', 'message_add_friend_success');

        return $this->redirectToRoute('profile', ['id' => $user->getId()]);
    }

    /**
     * Search action.
     *
     * @param string $type Type
     * @param User   $user User
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
    public function acceptFriend($type, User $user): Response
    {
        $currentUser = $this->getUser();
        $invitation = $this->friendService->getInvitation($user, $currentUser); //musi przyjść od kogoś do zalogowanego
        if (null === $invitation || $invitation->getAccepted() === true) {
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }

        if ('accept' === $type) {
            $this->friendService->acceptInvitation($invitation);
            $this->addFlash('success', 'message_accept_friend_success');
        } elseif ('decline' === $type) {
            $this->friendService->delete($invitation);
            $this->addFlash('success', 'message_decline_friend_success');
        }

        return $this->redirectToRoute('profile', ['id' => $user->getId()]);
    }

    /**
     * Search action.
     *
     * @param Request          $request          HTTP request
     * @param User             $user             User
     * @param FriendRepository $friendRepository Repository
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/{id}/friends",
     *     methods={"GET"},
     *     name="friends",
     * )
     */
    public function friends(Request $request, User $user, FriendRepository $friendRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $this->friendService->createPaginatedFriendsList($page, $user);

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
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/friend/requests",
     *     methods={"GET"},
     *     name="friend_requests",
     * )
     */
    public function friendRequests(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $this->friendService->createPaginatedInvitationsList($page);

        return $this->render(
            'requests.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}
