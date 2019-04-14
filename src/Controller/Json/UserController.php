<?php

namespace App\Controller\Json;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @Route("/json/user")
 */
class UserController
{
    private $router;

    private $security;

    private $userRepo;

    private $entityManager;

    private $tokenManager;

    public function __construct(
        RouterInterface $router,
        Security $security,
        UserRepository $userRepo,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $tokenManager
    )
    {
        $this->router = $router;
        $this->security = $security;
        $this->userRepo = $userRepo;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @Route("/followuser", name="json_user_followuser", methods={"POST"})
     */
    public function followuser(Request $request)
    {
        // check for csrf token
        if(!$this->tokenManager->isTokenValid( new CsrfToken('follow-unfollow-user', $request->request->get('token')) )) {
            return new RedirectResponse(
                $this->router->generate('main_homepage')
            );
        }

        $user_id = (int)$request->request->get('profile');

        if(null === ($authUser = $this->security->getUser()) || !is_int($user_id)) {
            return new RedirectResponse(
                $this->router->generate('main_homepage')
            );
        }

        $user = $this->userRepo->findOneBy(['id' => $user_id]);

        if(!\is_object($user) || $user === $authUser || $user->getFollowers()->contains($authUser)) {
            return new RedirectResponse(
                $this->router->generate('main_homepage')
            );
        }

        // this auth user is now following this profile
        $authUser->getFollowing()->add($user);
        
        $this->entityManager->persist($authUser);
        $this->entityManager->flush();

        return new RedirectResponse(
            $this->router->generate('main_homepage')
        );
    }
}