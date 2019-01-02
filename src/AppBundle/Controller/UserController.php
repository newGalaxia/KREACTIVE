<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Services\OmdbApiService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    private $omdbapiService;

    public function __construct(OmdbApiService $omdbApiService)
    {
        $this->omdbapiService = $omdbApiService;
    }

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

    /**
     * @Route("/user", name="post_user", methods={"POST"})
     */
    public function createUser(Request $request)
    {
        $content = [];
        if ($request->getContent()) {
            $content = json_decode($request->getContent(), true);
        }
        $user = New User();
        if (isset($content['pseudo'])) {
            $user->setPseudo($content['pseudo']);
        }
        if (isset($content['email'])) {
            $user->setEmail($content['email']);
        }
        if (isset($content['birthday'])) {
            $user->setBirthday($content['birthday']);
        }

        $user->setCreatedAt(new \DateTime());
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->persist($user);
        $entityManager->flush();

        return New JsonResponse("user saved with id :".$user->getId(),201);
    }

    private function getFilmList($choices)
    {
        $films = [];
        foreach($choices as $choice){
            $films = [ $choice->getFilm()->getImdbId() =>
                [
                    "title" => $choice->getFilm()->getTitle(),
                    "poster" => $choice->getFilm()->getPoster()
                ]
            ];
        }
        return $films;
    }

}
