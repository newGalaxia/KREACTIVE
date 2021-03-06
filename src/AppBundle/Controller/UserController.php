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
     * @Route("/users", name="users_list", methods={"GET"})
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
     * @Route("/users/{idFilm}", name="users_list_by_film")
     * @Method({"GET"})
     */
    public function getUsersByIdFilmAction(Request $request)
    {
        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find();

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

        $isValid = $this->checkData($content);

        if (!$isValid) {
            return New JsonResponse("erreur : données manquantes ou erronées",401);

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
            $films[$choice->getFilm()->getImdbId()] =
                [
                    "title" => $choice->getFilm()->getTitle(),
                    "poster" => $choice->getFilm()->getPoster()
                ];
        }
        return $films;
    }

    private function checkData($content)
    {
        if (isset($content['pseudo']) AND isset($content['email'])) {
            if (preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $content["email"])) {
                return true;
            }
        }
        return false;
    }

}
