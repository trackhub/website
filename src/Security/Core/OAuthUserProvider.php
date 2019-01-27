<?php


namespace App\Security\Core;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OAuthUserProvider extends FOSUBUserProvider
{
    /**
     * @var array
     */
    protected $properties = array(
        'identifier' => 'id',
    );

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager fOSUB user provider
     * @param array $properties property mapping
     */
    public function __construct(UserManagerInterface $userManager, array $properties)
    {
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
            $this->userManager->updateUser($user);

            $user = $this->loadUserByUsername($response->getNickname());
        }

        return $user;
    }
}
