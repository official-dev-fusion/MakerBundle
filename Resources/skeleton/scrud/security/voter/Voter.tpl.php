<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use <?= $entity_full_class_name ?>;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class <?= $class_name ?> extends Voter
{
    const SEARCH = '<?= $route_name ?>_search';
<?php if ($config['create']['activate']): ?>
    const CREATE = '<?= $route_name ?>_create';
<?php endif ?>
<?php if ($config['read']['activate']): ?>
    const READ = '<?= $route_name ?>_read';
<?php endif ?>
<?php if ($config['update']['activate']): ?>
    const UPDATE = '<?= $route_name ?>_update';
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
    const DELETE = '<?= $route_name ?>_delete';
<?php endif ?>
    /**
     * @var Security
     */
    private $security;
    
    /** 
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports($attribute, $subject)
    {        
        return in_array($attribute, [
            self::SEARCH,
<?php if ($config['create']['activate']): ?>
            self::CREATE,
<?php endif ?>
<?php if ($config['read']['activate']): ?>
            self::READ,
<?php endif ?>
<?php if ($config['update']['activate']): ?>
            self::UPDATE,
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
            self::DELETE
<?php endif ?>
        ]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::SEARCH:
                return $this->canSearch($subject, $user);
<?php if ($config['create']['activate']): ?>
            case self::CREATE:
                return $this->canCreate($subject, $user);
<?php endif ?>
<?php if ($config['read']['activate']): ?>
            case self::READ:
                return $this->canRead($subject, $user);
<?php endif ?>
<?php if ($config['update']['activate']): ?>
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
            case self::DELETE:
                return $this->canDelete($subject, $user);
<?php endif ?>
        }
        throw new \LogicException('This code should not be reached!');
    }
    
    private function canSearch($subject, User $user)
    {
        return true;
    }

<?php if ($config['create']['activate']): ?>
    private function canCreate($subject, User $user)
    {
        return true;
    }

<?php endif ?>
<?php if ($config['read']['activate']): ?>
    private function canRead($subject, User $user)
    {
        return true;
    }

<?php endif ?>
<?php if ($config['update']['activate']): ?>
    private function canUpdate($subject, User $user)
    {
        return false;
    }

<?php endif ?>
<?php if ($config['delete']['activate']): ?>
    private function canDelete($subject, User $user)
    {
        return false;
    }
<?php endif ?>
}
