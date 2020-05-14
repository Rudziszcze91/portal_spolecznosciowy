<?php
/**
 * Web controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
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
     * Index action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="index",
     * )
     */
    public function index(Request $request): Response
    {
        return $this->render(
            'index.html.twig'
        );
    }

    /**
     * Search action.
     *
     * @param Request            $request        HTTP request
     * @param UserRepository     $userRepository HTTP request
     * @param PaginatorInterface $paginator      Paginator
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
}
