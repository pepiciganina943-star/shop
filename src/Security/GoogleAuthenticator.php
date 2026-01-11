<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // Continue ONLY if the route is the check URL
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                // Fetch user info from Google
                /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();
                $googleId = $googleUser->getId();

                // Check if user already exists by email
                $existingUser = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if ($existingUser) {
                    // User exists - link Google account if not already linked
                    if (!$existingUser->getGoogleId()) {
                        $existingUser->setGoogleId($googleId);
                        $this->entityManager->flush();
                    }
                    return $existingUser;
                }

                // Check if user exists by Google ID
                $existingUserByGoogleId = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['googleId' => $googleId]);

                if ($existingUserByGoogleId) {
                    return $existingUserByGoogleId;
                }

                // Create new user
                $user = new User();
                $user->setEmail($email);
                $user->setGoogleId($googleId);
                $user->setRoles(['ROLE_USER']);

                // Set a random password (user won't use it for Google login)
                $randomPassword = bin2hex(random_bytes(32));
                $hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // 1. Първо проверяваме ролята на потребителя
        if (in_array('ROLE_ADMIN', $token->getUser()->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('admin'));
        }

        // 2. Проверяваме дали има запазен път (например ако е искал да влезе в количката преди логин)
        $targetPath = $request->getSession()->get('_security.main.target_path');

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        // 3. По подразбиране пращаме към началната страница
        return new RedirectResponse($this->router->generate('app_home'));
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new RedirectResponse(
            $this->router->generate('app_login', ['error' => $message])
        );
    }
}
