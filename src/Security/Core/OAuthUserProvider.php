<?php


namespace App\Security\Core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
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

class OAuthUserProvider implements UserProviderInterface, AccountConnectorInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var array
     */
    protected $properties = array(
        'identifier' => 'id',
    );

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

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
    public function loadUserByUsername($username)
    {
        // Compatibility with FOSUserBundle < 2.0
        if (class_exists('FOS\UserBundle\Form\Handler\RegistrationFormHandler')) {
            return $this->userManager->loadUserByUsername($username);
        }

        return $this->userManager->findUserByUsername($username);
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

            $user = $this->loadUserByUsername($username);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }

        $property = $this->getProperty($response);
        $username = $response->getUsername();

        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $this->disconnect($previousUser, $response);
        }

        if ($this->accessor->isWritable($user, $property)) {
            $this->accessor->setValue($user, $property, $username);
        } else {
            throw new \RuntimeException(sprintf('Could not determine access type for property "%s".', $property));
        }

        $this->userManager->updateUser($user);
    }

    /**
     * Disconnects a user.
     *
     * @param UserInterface $user
     * @param UserResponseInterface $response
     */
    public function disconnect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);

        $this->accessor->setValue($user, $property, null);
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        // Compatibility with FOSUserBundle < 2.0
        if (class_exists('FOS\UserBundle\Form\Handler\RegistrationFormHandler')) {
            return $this->userManager->refreshUser($user);
        }

        $identifier = $this->properties['identifier'];
        if (!$user instanceof User || !$this->accessor->isReadable($user, $identifier)) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }

        $userId = $this->accessor->getValue($user, $identifier);
        if (null === $user = $this->userManager->findUserBy(array($identifier => $userId))) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $userId));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    /**
     * Gets the property for the response.
     *
     * @param UserResponseInterface $response
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function getProperty(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        return $this->properties[$resourceOwnerName];
    }
}
