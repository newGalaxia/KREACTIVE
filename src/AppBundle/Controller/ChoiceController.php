<?php

namespace AppBundle\Controller;
use AppBundle\Entity\Film;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Choice;
use AppBundle\Services\OmdbApiService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ChoiceController extends Controller
{
    private $omdbapiService;

    public function __construct(OmdbApiService $omdbApiService)
    {
        $this->omdbapiService = $omdbApiService;
    }

    /**
     *
     * @Route("/choice", name="post_choice", methods={"POST"})
     */
    public function createChoice(Request $request)
    {
        $content = [];
        if ($request->getContent()) {
            $content = json_decode($request->getContent(), true);
        }

        if ( isset($content['user_id']) && isset($content['film_imdbId']) ) {
            $choice = New Choice();

            // check if user exist and set idUser value
            $choice = $this->fillUser($choice, $content['user_id']);
            if ($choice === false) {
                return New JsonResponse("echec : cet utilisateur n'existe pas", 401);
            }

            //choices number must be < 3
            $nbChoices = $this->checkChoicesNumber($content['user_id']);
            if ($nbChoices === false) {
                return New JsonResponse("echec : vous ne pouvez pas choisir plus de 3 films", 401);
            }
            //check if film exist in api omdb
            $film = $this->omdbapiService->getFilm($content['film_imdbId']);
            if ($film === false ) {
                return New JsonResponse("echec : ce film n'existe pas", 401);
            } else {
                $film = $this->updateFilmList($film);
                $choice->setFilm($film);
            }
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->persist($choice);
            $entityManager->flush();

            return New JsonResponse("bravo vous avez enregistré votre choix ", 201);

        } else {
            return New JsonResponse("echec : paramètres manquants ", 401);
        }
    }

    /**
     * @Route("/choicesByUser/{id}", name="get_choices_user", methods={"GET"})
     */
    public function getChoicesByUserId($id)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($id);
        $formatted = [];
        foreach($user->getChoices() as $choice){
            $formatted[ $choice->getFilm()->getImdbId()] =  [
                    $choice->getFilm()->getTitle(),
                    $choice->getFilm()->getPoster()
                ];
        }
        return new JsonResponse($formatted);

    }

    /**
     * @Route("/UsersByFilm/{idFilm}", name="get_users_film", methods={"GET"})
     */
    public function getUsersByFilm($idFilm)
    {
        $film = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Film')
            ->findBy(["imdbId" => $idFilm]);

        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Choice')
            ->findBy(["film" => $film ]);
        $formatted = [];
        foreach($user->getChoices() as $choice){
            $formatted[ $choice->getFilm()->getImdbId()] =  [
                $choice->getFilm()->getTitle(),
                $choice->getFilm()->getPoster()
            ];
        }
        return new JsonResponse($formatted);

    }

    /**
     * @Route("/bestFilm", name="get_best_film", methods={"GET"})
     */
    public function getBestChoice()
    {
        $bestFilm = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Choice')
            ->findBestFilm();

    }



    /**
     * @Route("/choice/{id}", name="delete_choice", methods={"DELETE"})
     */
    public function deleteChoice($id)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $choice = $entityManager
            ->getRepository('AppBundle:Choice')
            ->find($id);
        if($choice !== null){
            $entityManager->remove($choice);
            $entityManager->flush();
            return New JsonResponse("suppression réussie", 201);
        }
        return New JsonResponse("Ressource introuvable", 401);
    }

    /**
     * update choice entity with user if exist
     * @param $choice
     * @param $userId
     * @return bool
     */
    private function fillUser($choice, $userId)
    {
        if (isset($userId)) {
            $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($userId);

            if ($user !== null ) {
                $choice->setUser($user);

                return $choice;
            } else {

                return false;
            }
        }
    }

    /**
     * insert the data film if it is not in database
     * @param $newFilm
     * @return Film
     */
    private function updateFilmList($newFilm)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $film = $entityManager
            ->getRepository('AppBundle:Film')
            ->findBy(["imdbId" => $newFilm['imdbID']]);

        if ($film === null) {
            $filmToSave = new Film();
            $filmToSave->setImdbId($newFilm['imdbID']);
            $filmToSave->setTitle($newFilm['Title']);
            $filmToSave->setPoster($newFilm['Poster']);

            $entityManager->persist($filmToSave);
            $entityManager->flush();

            return $filmToSave;
        }

        return $film;
    }

    private function checkChoicesNumber($userId){
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $user = $entityManager
            ->getRepository('AppBundle:User')
            ->find($userId);

        $choices = $entityManager
            ->getRepository('AppBundle:Choice')
            ->findBy(["user" => $user]);
        if(count($choices) === 3) {
            return false;
        };
        return true;
    }

}