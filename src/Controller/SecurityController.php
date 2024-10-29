<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\JWTService;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // } ici si on décommente et qu'on est connecté on peut être redirigé en fonction de ce que l'on met à la place de target_path

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // on crée une nouvelle route pour réinitialiser le mdp, ensuite aller dans template/login
    // ensuite je vais refaire un formulaire à l'aide de synfony console make:form
    #[Route('/mot-de-passe-oublie', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        JWTService $jwt,
        SendEmailService $mail
    ): Response {
        // j'ai besoin de la classe ResetPasswordRequestFormType que j'ai créé
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        // pour gérer le form, on fait appel à Request de httpFundation ou  on va récupérer le post de mon form pour pouvoir le manipuler
        $form->handleRequest($request);

        // ensuite je peux verifier si mon formulaire est envoyé:
        if ($form->isSubmitted() && $form->isValid()) {
            // le formulaire est envoyé et valide, on va chercher l'utilisateur dans la bdd donc on a besoin de userRepository, on utilise l'email du form et getdata
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // on vérifie si on a bien un user: 
            if ($user) {
                // on a un utilisateur
                // on génère un token(jwt), on prend le même code du registrationController où on génère un token
                $header = [
                    'typ' => 'JWT',
                    'alg' => 'HS256'
                ];

                $payload = [
                    'user_id' => $user->getId()
                ];

                $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
                // on va pouvoir passé à l'envoi du mail, dans registrationController on gènere l'url avec la page twig, ici on va le faire directement avec le controller mais pour cela il faut une nouvelle route avc /mot-de-passe-oublie/{token}

                // on génère l'url vers reset_password, pour ce faire j'ai besoin de urlgenratorInterface, pour pouvoir généré une url absolu cad complete avec https
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // on envoie l'email: 
                $mail->send(
                    'no-reply@openblog.test',
                    $user->getEmail(), //c'est le destinataire
                    'Récupération de mot de passe sur le site OpenBlog', // le titre du mail 
                    'password_reset', // le nom du template pour récupérer la page twig
                    compact('user', 'url') // le contexte; équivaut à :['user' => $user, 'url'=>$url]
                );

                // on envoie un message
                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }
            // $user est null, qu'on le retrouve par à partir de l'email , on fait un addflash et on redirige
            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }



        // je passe à ma page twig mon formulaire sous la form du var requestPassForm
        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    // ici on est dans la route après avoir cliqué sur le lien du mail:
    // on récupère le token, on fais pareil quand verifUser, on vérifie si il est valid ect.
    #[Route('/mot-de-passe-oublie/{token}', name: 'reset_password')]
    public function resetPassword(
        $token,
        JWTService $jwt,
        UsersRepository $usersRepository,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // Le token est valide
            // On récupère les données (payload)
            $payload = $jwt->getPayload($token);

            // On récupère le user en faisant un find et récupérant le user_id du payload
            $user = $usersRepository->find($payload['user_id']);

            // je fais un form à partir du bundler que j'appelle ResetPasswordFormType
            if ($user) {
                $form = $this->createForm(ResetPasswordFormType::class);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // on doit hasher le mdp on fait appel à UserPasswordHasherInterface, il faut que l'input dans mon formulaire s'appele password dans ResetPasswordFormType
                    $user->setPassword(
                        $passwordHasher->hashPassword($user, $form->get('password')->getData())
                    );

                    // mtn que le mdp est valid il faut l'enregistrer en bdd: on fait appel à $em
                    $em->flush();

                    $this->addFlash('success', 'Mot de passe changé avec succès');
                    return $this->redirectToRoute('app_login');
                }
                // si on a un user il faut envoyé le formulaire, et on lui envoie le form sous passForm
                return $this->render('security/reset_password.html.twig', [
                    'passForm' => $form->createView()
                ]);
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
