<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager, // <--- За CSRF
        ValidatorInterface $validator // <--- За reCAPTCHA
    ): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $submittedToken = $request->request->get('_token');

            // 1. ПРОВЕРКА НА CSRF
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('register_process', $submittedToken))) {
                $error = 'Невалиден токен за сигурност (CSRF). Презаредете страницата.';
            }

            // 2. ПРОВЕРКА НА RECAPTCHA
            // Използваме Validator-а на Symfony, за да проверим само този токен



            // 3. Ако всичко е наред -> Създаваме юзъра
            if (!$error) {
                $user = new User();
                $user->setEmail($email);
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        // Трябва да вземем Site Key от .env, за да го пратим на Twig
        // (Пакетът го пази в параметър, но можеш да го вземеш и директно от $_ENV ако е по-лесно, но ето правилния начин)

        return $this->render('registration/register.html.twig', [
            'error' => $error,

        ]);
    }
}