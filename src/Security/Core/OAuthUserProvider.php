<?php


namespace App\Security\Core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use FOS\UserBundle\Model\User;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUserProvider extends FOSUBUserProvider
{
    /**
     * @var array
     */
    protected $properties = array(
        'identifier' => 'id',
    );

    private $entityManager;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager fOSUB user provider
     * @param array $properties property mapping
     */
    public function __construct(UserManagerInterface $userManager, EntityManagerInterface $repo, array $properties)
    {
        $this->entityManager = $repo;
        $this->userManager = $userManager;
        $this->properties = array_merge($this->properties, $properties);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();

        if (empty($username)) {
            throw new AccountNotLinkedException(sprintf("User '%s' not found.", $username));
        }

        $field = $this->getProperty($response);
        $user = $this->userManager->findUserBy(array($field => $username));
        if (!$user) {
            $user = new \App\Entity\User\User();
            $user->setFacebookId($username);
            // @TODO check if username already exists
            $user->setUsername($response->getNickname());
            $user->setEmail($response->getEmail());
            $user->setPassword(uniqid());
            $user->setEnabled(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $user = $this->loadUserByUsername($response->getNickname());
        }

        return $user;
    }
}
