<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\StoryRepository;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MainController
{

    private $twig;

    private $security;

    public function __construct(
        \Twig_Environment $twig,
        Security $security,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->twig = $twig;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * @Route("/", name="main_homepage")
     */
    public function homepage(StoryRepository $storyRepo, AuthenticationUtils $authenticationUtils)
    {
        if (($authUser = $this->security->getUser()) instanceof User) {
            // generate top stories from auth user followings
            $stories = $storyRepo->findAllByFollowings($authUser->getFollowing());

            $view_stories = [];
            if (count($stories)) {
                foreach ($stories as $story) {
                    // append main story object
                    if (isset($story[0])) {
                        $view_stories[] = $story[0];
                    }
                }
            }
        } else {
            // generate top stories overall and login sign-up panel

            // collect info for login form
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();

            // generate working registration form
            $userRegistration = new User();
            $formRegistration = $this->formFactory->create( RegistrationFormType::class, $userRegistration, [
                'action' => $this->router->generate('app_register'),
                'method' => 'POST'
            ]);
        }

        $response = $this->twig->render('main/homepage.html.twig', [
            'stories' => $view_stories ?? [],
            'registrationForm' => isset($formRegistration) ? $formRegistration->createView(): '',
            'error' => $error ?? '',
            'last_username' => $lastUsername ?? '',
        ]);

        return new Response($response);
    }

}
