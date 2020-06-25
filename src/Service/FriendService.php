<?php
/**
 * Friend service.
 */

namespace App\Service;

use App\Entity\Friend;
use App\Entity\User;
use App\Repository\FriendRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class FriendService.
 */
class FriendService
{
    /**
     * Friend repository.
     *
     * @var \App\Repository\FriendRepository
     */
    private $friendRepository;

    /**
     * Paginator.
     *
     * @var \Knp\Component\Pager\PaginatorInterface
     */
    private $paginator;

    /**
     * Token Storage Interface.
     *
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * FriendService constructor.
     *
     * @param \App\Repository\FriendRepository        $friendRepository Friend repository
     * @param \Knp\Component\Pager\PaginatorInterface $paginator        Paginator
     * @param TokenStorageInterface                   $token            Token to get current user
     */
    public function __construct(FriendRepository $friendRepository, PaginatorInterface $paginator, TokenStorageInterface $token)
    {
        $this->friendRepository = $friendRepository;
        $this->paginator = $paginator;
        $this->token = $token;
    }

    /**
     * Get Friend Connection.
     *
     * @param User $user1 User
     * @param User $user2 User
     *
     * @return int|mixed|string|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFriendConnection(User $user1, User $user2)
    {
        return $this->friendRepository->getFriendConnection($user1, $user2);
    }

    /**
     * Count friends.
     *
     * @param User $user User
     *
     * @return int|void
     */
    public function countFriends(User $user)
    {
        $friends = $this->friendRepository->getFriends($user)->getQuery()->getResult();

        return count($friends);
    }

    /**
     * Make Friend Connection.
     *
     * @param User $currentUser Current User
     * @param User $user        User
     *
     * @return void
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addFriendConnection(User $currentUser, User $user)
    {
        $connection = new Friend();
        $connection->setAccepted(false);
        $connection->setFromUser($currentUser);
        $connection->setToUser($user);
        $this->save($connection);
    }

    /**
     * Get Invitation.
     *
     * @param User $user        User
     * @param User $currentUser Current User
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getInvitation(User $user, User $currentUser)
    {
        return $this->friendRepository->getInvitation($user, $currentUser);
    }

    /**
     * Get Invitation.
     *
     * @param Friend $connection Friend
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function acceptInvitation(Friend $connection)
    {
        $connection->setAccepted(true);
        $this->save($connection);
    }

    /**
     * Create paginated list.
     *
     * @param int  $page Page number
     * @param User $user User
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface Paginated list
     */
    public function createPaginatedFriendsList(int $page, User $user): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->friendRepository->getFriends($user),
            $page,
            User::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Create paginated list.
     *
     * @param int $page Page number
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface Paginated list
     */
    public function createPaginatedInvitationsList(int $page): PaginationInterface
    {
        $user = $this->token->getToken()->getUser();

        return $this->paginator->paginate(
            $this->friendRepository->getInvitations($user),
            $page,
            User::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save.
     *
     * @param Friend $friend Friend
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Friend $friend)
    {
        $this->friendRepository->save($friend);
    }

    /**
     * Delete.
     *
     * @param Friend $friend Friend
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Friend $friend)
    {
        $this->friendRepository->delete($friend);
    }
}
