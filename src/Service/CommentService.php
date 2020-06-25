<?php
/**
 * Comment service.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class CommentService.
 */
class CommentService
{
    /**
     * Comment repository.
     *
     * @var \App\Repository\CommentRepository
     */
    private $commentRepository;

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
     * CommentService constructor.
     *
     * @param \App\Repository\CommentRepository       $commentRepository Comment repository
     * @param \Knp\Component\Pager\PaginatorInterface $paginator         Paginator
     * @param TokenStorageInterface                   $token             Token to get current user
     */
    public function __construct(CommentRepository $commentRepository, PaginatorInterface $paginator, TokenStorageInterface $token)
    {
        $this->commentRepository = $commentRepository;
        $this->paginator = $paginator;
        $this->token = $token;
    }

    /**
     * check user.
     *
     * @param User $user User
     *
     * @return bool
     */
    public function checkUser(User $user)
    {
        if ($this->token->getToken()->getUser() !== $user) {
            return false;
        }

        return true;
    }

    /**
     * Save.
     *
     * @param Comment $comment Comment
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Comment $comment)
    {
        $user = $this->token->getToken()->getUser();
        $comment->setUser($user);
        $this->commentRepository->save($comment);
    }

    /**
     * Delete.
     *
     * @param Comment $comment Comment
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Comment $comment)
    {
        $this->commentRepository->delete($comment);
    }
}
