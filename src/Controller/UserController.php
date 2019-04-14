<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController
{

    private $twig;

    private $security;

    public function __construct(
        \Twig_Environment $twig,
        Security $security
    )
    {
        $this->security = $security;
        $this->twig = $twig;
    }

    /**
     * @Route("/{id}", name="user_viewuser", requirements={"id"="\d+"})
     */
    public function viewuser(User $profile)
    {
        return new Response(
            $this->twig->render('user/viewuser.html.twig', ['profile' => $profile])
        );
    }
}