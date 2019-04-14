<?php
namespace App\Security;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use App\Entity\User;
use App\Entity\Story;
use App\Entity\Comment;

class StoryVoter extends Voter
{
    const EDIT = 'edit';

    const REMOVE = 'remove';

    private $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager)
    {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    public function supports($attribute, $subject)
    {
        if(!$subject instanceof Story && !$subject instanceof Comment) {
            return false;
        }

        if(!\in_array($attribute, [ self::EDIT, self::REMOVE ])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $story, TokenInterface $token)
    {
        if(!$token->getUser() instanceof User) {
            return false;
        }

        switch($attribute) {
            case self::EDIT:
                return $this->canView($story, $token);
            case self::REMOVE:
                return $this->canRemove($story, $token);
        }

        return false;
    }

    private function canView($story, $token)
    {
        return $story->getUser() === $token->getUser();
    }
    
    private function canRemove($story, $token)
    {
        return $story->getUser() === $token->getUser();
    }
}