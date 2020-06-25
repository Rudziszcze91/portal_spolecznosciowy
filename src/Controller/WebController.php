<?php
/**
 * Web controller.
 */

namespace App\Controller;

use App\Repository\FriendRepository;
use App\Service\UserService;
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
     * Post service.
     *
     * @var \App\Service\UserService
     */
    private $userService;

    /**
     * CategoryController constructor.
     *
     * @param \App\Service\UserService $userService User service
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="index",
     * )
     */
    public function index(): Response
    {
        return $this->render(
            'index.html.twig'
        );
    }

    /**
     * Search action.
     *
     * @param Request          $request          HTTP request
     * @param FriendRepository $friendRepository Repository
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/search",
     *     methods={"GET"},
     *     name="search",
     * )
     */
    public function search(Request $request, FriendRepository $friendRepository): Response
    {
        $phrase = $request->query->get('phrase');
        $page = $request->query->getInt('page', 1);
        $pagination = $this->userService->createPaginatedUsers($page, $phrase);

        return $this->render(
            'search.html.twig',
            [
                'pagination' => $pagination,
                'friendRepository' => $friendRepository,
                'phrase' => $phrase,
            ]
        );
    }
}
