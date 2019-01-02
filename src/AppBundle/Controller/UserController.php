<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/users", name="users_list")
     * @Method({"GET"})
     */
    public function getUsersAction(Request $request)
    {
        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findAll();

        $formatted = [];
        foreach ($users as $user) {
           $films = $this->getFilmList($user->getChoices());
            $formatted[] = [
                'id' => $user->getId(),
                'pseudo' => $user->getPseudo(),
                'choices' => $films,
            ];
        }

        return new JsonResponse($formatted);
    }

    private function getFilmList($choices)
    {
        $films = [];
        foreach($choices as $choice){
            $films = [$choice->getFilm()->getImdbId() => $choice->getFilm()->getTitle()];
        }
        return $films;
    }
}
