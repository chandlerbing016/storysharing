<?php

namespace App\Controller\Json;

use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/json/story")
 */
class StoryController
{

    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/tags/{tag}", name="json_story_tags")
     */
    public function tags($tag, TagRepository $tagRepo)
    {
        // echo '<pre>';
        // \Doctrine\Common\Util\Debug::dump($suggestion);
        // echo '</pre>';

        $suggestions = $tagRepo->findTitleLike($tag);
        $suggestionsResponse = [];

        if (count($suggestions)) {
            foreach ($suggestions as $suggestion) {
                if (isset($suggestion['title'])) {
                    $suggestionsResponse[] = $suggestion['title'];
                }
            }
        }

        return new JsonResponse($suggestionsResponse);
    }
}
