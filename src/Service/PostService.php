<?php
/**
 * Post service.
 */

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PostService.
 */
class PostService
{
    /**
     * Post repository.
     *
     * @var \App\Repository\PostRepository
     */
    private $postRepository;

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
     * PostService constructor.
     *
     * @param \App\Repository\PostRepository          $postRepository Post repository
     * @param \Knp\Component\Pager\PaginatorInterface $paginator      Paginator
     * @param TokenStorageInterface                   $token          Token to get current user
     */
    public function __construct(PostRepository $postRepository, PaginatorInterface $paginator, TokenStorageInterface $token)
    {
        $this->postRepository = $postRepository;
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
     * Create paginated list.
     *
     * @param int  $page Page number
     * @param User $user User
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface Paginated list
     */
    public function createPaginatedUsersPosts(int $page, User $user): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->postRepository->userPosts($user),
            $page,
            Post::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Create Post.
     *
     * @param Post $post Post
     * @param User $user User
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createPost(Post $post, User $user)
    {
        $post->setUser($user);
        $this->save($post);
    }

    /**
     * Save.
     *
     * @param Post $post Post
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Post $post)
    {
        $this->postRepository->save($post);
    }

    /**
     * Delete.
     *
     * @param Post $post Post
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Post $post)
    {
        $this->postRepository->delete($post);
    }
}
