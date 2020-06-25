<?php
/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Service\FriendService;
use App\Service\PostService;
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
     * Post service.
     *
     * @var \App\Service\PostService
     */
    private $postService;

    /**
     * Friend service.
     *
     * @var \App\Service\FriendService
     */
    private $friendService;

    /**
     * CategoryController constructor.
     *
     * @param \App\Service\PostService   $postService   Post service
     * @param \App\Service\FriendService $friendService Friend service
     */
    public function __construct(PostService $postService, FriendService $friendService)
    {
        $this->postService = $postService;
        $this->friendService = $friendService;
    }

    /**
     * Profile action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User
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
    public function profile(Request $request, User $user): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $logged = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $pagination = $this->postService->createPaginatedUsersPosts($page, $user);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($logged === $user) {
                $this->addFlash('danger', 'message_created_failed');

                return $this->redirectToRoute('profile', ['id' => $user->getId()]);
            }
            $this->postService->createPost($post, $user);
            $this->addFlash('success', 'message_created_successfully');

            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }
        $connection = $this->friendService->getFriendConnection($user, $logged);
        $friendsNumber = $this->friendService->countFriends($user);

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
