<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Story;
use App\Entity\Tag;
use App\Entity\Visitor;
use App\Entity\User;
use App\Form\StoryType;
use App\Repository\CommentRepository;
use App\Repository\StoryRepository;
use App\Repository\VisitorRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route("/story")
 */
class StoryController
{

    private $twig;

    private $security;

    private $router;

    private $formFactory;

    private $entityManager;

    private $storyRepo;

    public function __construct(
        \Twig_Environment $twig,
        Security $security,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        StoryRepository $storyRepo,
        CsrfTokenManagerInterface $tokenManager
    ) {
        $this->twig = $twig;
        $this->security = $security;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->storyRepo = $storyRepo;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @Route("/add", name="story_add")
     */
    public function add(Request $request, TagRepository $tagRepo)
    {
        if (null === ($user = $this->security->getUser())) {
            return new RedirectResponse(
                $this->router->generate('app_login')
            );
        }

        // new tag suggestion
        if ($request->isMethod('POST') && '1' === $request->request->get('tag-suggestion')) {
            if (!$this->tokenManager->isTokenValid(new CsrfToken('tag-suggestion', $request->request->get('token')))) {
                throw new NotFoundHttpException("invalid page request");
            }

            $tag = (string) $request->request->get('tag');
            $tag = filter_var($tag, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);

            if(!$tag) {
                throw new NotFoundHttpException("invalid tag suggestion");
            } else {
                $this->persistTag($tag, $user);
            }
        }

        $story = new Story();

        $storyForm = $this->formFactory->create(StoryType::class, $story);

        $storyForm->handleRequest($request);

        // the content field is presisted automatically
        if ($storyForm->isSubmitted() && $storyForm->isValid()) {

            // we need to presist the author
            $story->setUser($user);

            // get and verify tags
            $tags = (string) $request->request->get('tags');
            $tags = array_filter(array_map('trim', explode(',', $tags)));

            // verify the tags, and set to the story [ pass story by reference ]
            if(count($tags) === 0 || !$this->verifyAndSetTags($tags, $tagRepo, $story)) {
                throw new NotFoundHttpException("could not verify tags");
            }

            $this->entityManager->persist($story);
            $this->entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('main_homepage')
            );
        }

        $response = $this->twig->render('story/add.html.twig', [
            'form' => $storyForm->createView(),
        ]);

        $this->entityManager->flush();

        return new Response($response);
    }

    /**
     * @Route("/edit/{id}", name="story_edit", requirements={"id"="\d+"})
     */
    public function edit($id, Request $request)
    {
        if (null === ($user = $this->security->getUser())) {
            return new RedirectResponse(
                $this->router->generate('app_login')
            );
        }

        // new tag suggestion
        if ($request->isMethod('POST') && '1' === $request->request->get('tag-suggestion')) {
            if (!$this->tokenManager->isTokenValid(new CsrfToken('tag-suggestion', $request->request->get('token')))) {
                throw new NotFoundHttpException("invalid page request");
            }
            $tag = (string) $request->request->get('tag');
            $this->persistTag($tag, $user);
        }

        $story = $this->storyRepo->findOneBy(['id' => $id]);

        if (false === $this->security->isGranted('edit', $story)) {
            return new RedirectResponse(
                $this->router->generate('main_homepage')
            );
        }

        $storyForm = $this->formFactory->create(StoryType::class, $story);

        $storyForm->handleRequest($request);

        // the content field is presisted automatically
        if ($storyForm->isSubmitted() && $storyForm->isValid()) {

            // we need to presist the author
            $story->setUser($user);

            $this->entityManager->persist($story);
            $this->entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('main_homepage')
            );
        }

        $response = $this->twig->render('story/add.html.twig', [
            'form' => $storyForm->createView(),
        ]);

        return new Response($response);
    }

    /**
     * @Route("/delete/{id}", name="story_delete", requirements={"id"="\d+"})
     */
    public function delete($id, Request $request)
    {
        if (null === $this->security->getUser()) {
            return new RedirectResponse(
                $this->router->generate('app_login')
            );
        }

        $story = $this->storyRepo->findOneBy(['id' => $id]);

        if (false === $this->security->isGranted('remove', $story)) {
            return new RedirectResponse(
                $this->router->generate('main_homepage')
            );
        }

        // delete this entity
        $this->entityManager->remove($story);
        $this->entityManager->flush();

        return new RedirectResponse(
            $this->router->generate('main_homepage')
        );
    }

    /**
     * @Route("/{id}", name="story_show", requirements={"id"="\d+"})
     */
    public function show($id, Request $request, VisitorRepository $visitorRepo, CommentRepository $commentRepo)
    {
        if (null === ($story = $this->storyRepo->findOneBy(['id' => $id]))) {
            throw new NotFoundHttpException("Page doesn't exists!");
        }

        // handle upvote and comment post request
        if ($request->isMethod('POST')) {
            if (null === ($user = $this->security->getUser())) {
                throw new NotFoundHttpException("Page doesn't exists!");
            }

            // handle upvote submission
            if ('1' == $request->request->get('submit_upvote')) {
                if (!$this->tokenManager->isTokenValid(new CsrfToken('upvote-story', $request->request->get('token')))) {
                    throw new NotFoundHttpException("Page doesn't exists!");
                }

                // if user has already upvoted this story
                if (!$user->getUpvotingStories()->contains($story)) {

                    $user->getUpvotingStories()->add($story);
                    $this->entityManager->persist($user);

                    $story->raiseUpvoteCounter();
                    $this->entityManager->persist($story);
                }
            }

            // handle comment submission
            if ('1' == $request->request->get('submit_comment')) {
                if (!$this->tokenManager->isTokenValid(new CsrfToken('comment-story', $request->request->get('token')))) {
                    throw new NotFoundHttpException("Page doesn't exists!");
                }
                $content = (string) $request->request->get('comment');

                $comment = new Comment();
                $comment->setContent($content);
                $comment->setStory($story);
                $comment->setUser($user);

                // if it's a reply. submit_comment_reply's an id
                if (null !== $request->request->get('submit_comment_reply')) {
                    $parentId = (int) $request->request->get('submit_comment_reply');
                    $parentComment = $commentRepo->findOneBy(['id' => $parentId]);
                    if (null !== $parentComment) {
                        $comment->setParent($parentComment);
                    }
                }

                $this->entityManager->persist($comment);
            }
        }

        // generate random ips; for testing purpose only
        // $randIP = "".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255);

        /**
         * try to find if the visitor already visited
         *
         * it's a sql query and it's not a scalable solution
         * it's costly.
         * we shall replace this by memcached based solution like stackoverflow
         * out of every 100 only 5-10 requests should be hitting the db
         */
        $visitor = $visitorRepo->findOneBy([
            // 'ip_address' => $randIP,
            'ip_address' => $request->getClientIp(),
            'story' => $story,
        ]);
        // if null, count as a unique visitor
        if (null === $visitor) {
            $visitor = new Visitor();
            $visitor->setStory($story);
            $visitor->setIpAddress($request->getClientIp());
            // $visitor->setIpAddress( $randIP );

            $story->raiseViewCounter();

            $this->entityManager->persist($story);
            $this->entityManager->persist($visitor);
        }

        $this->entityManager->flush();

        return new Response(
            $this->twig->render("story/show.html.twig", ['story' => $story])
        );
    }

    /**
     * @Route("/comment/remove/{id}", name="story_commentremove", requirements={"id"="\d+"})
     */
    public function commentremove(Comment $comment)
    {
        // improve this code
        if(!$this->security->isGranted('remove', $comment)) {
            throw new NotFoundHttpException("couldn't delete the comment");
        }
        $storyId = $comment->getStory()->getId();
        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return new RedirectResponse(
            $this->router->generate('story_show', ['id' => $storyId])
        );
    }

    // persist tag suggestion by this user
    private function persistTag(string $title, User $suggestor)
    {
        $tagEntity = new Tag();
        $tagEntity->setTitle($title);
        $tagEntity->setTagSuggestor($suggestor);

        // approve for now without any intervention
        $tagEntity->approve();

        $this->entityManager->persist($tagEntity);
    }

    // verify tags for this story
    private function verifyAndSetTags(array $tags, TagRepository $tagRepo, Story &$story)
    {
        $isOkay = true;
        foreach($tags as $tag) {
            $tagEntity = $tagRepo->findOneBy(['title' => $tag]);
            if(!$tagEntity instanceof Tag) {
                $isOkay = false; break;
            }

            $story->setTag($tagEntity);
        }

        return $isOkay;
    }
}
