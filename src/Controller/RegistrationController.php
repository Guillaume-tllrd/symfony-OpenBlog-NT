<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager, JWTService $jwt, SendEmailService $mail): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            // Générer le token, on doit créer un nouveau dossier 'service' dans src 
            // on crée le header de notre token: on peut aller voir sur jwt.io pour la construction du token
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // Payload, biensur on ne peut faire cette procédure avant le persist et le flush car on peut récupérer l'id nouvdelllement créer
            $payload = [
                'user_id' => $user->getId()
            ];

            // on génére le token mais pour cela il faut le JWTService qu'on rajoute comme paramètre dans la méthode register au dessus. On a pas mis le parametre validité cad que c'est la valeur par défzut de la méthode qui va fonctionné (3h)
            // Poir la éthode après le payload, il demande secret, on utilise la méthode getParameter pour aller chercher dans services.yaml les parameters app.jwtsecret
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));


            // Puis envoyer le token dans l'e-mail, je dois récupérer le service SendEmailService que je met dans les paramètres de la méthode
            $mail->send(
                'no-reply@openblog.test',
                $user->getEmail(), //c'est le destinataire
                'Activation de votre compte sur le site OpenBlog', // le titre du mail 
                'register', // le nom du template pour récupérer la page twig
                compact('user', 'token') // le contexte; équivaut à :['user' => $user, 'token'=>$token]
            );

            $this->addFlash('success', 'Utilisateur inscrit, veuillez cliquer sur le lien reçu pour confirmer votre adresse e-mail');

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    // Mtn il faut qu'on puisse activer le compyte qd on clique sur le lien, donc il faut créer une nouvelle route
    #[Route('/verif/{token}', name: 'verify_user')]
    // j'aurai besoin de mon token de jwtsevice pour vérifier le token, de userrepository pour demander à ma bdd si le user exist, et d'entitymanagerInterface pour pouvoir modifier mon user
    public function verifUser($token, JWTService $jwt, UsersRepository $usersRepository, EntityManagerInterface $em): Response
    {
        // On vérifie si le token est valide (cohérent, pas expiré et signature correcte), à l'aide des méthodes de jwtService
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // Le token est valide
            // On récupère les données (payload)
            $payload = $jwt->getPayload($token);

            // On récupère le user en faisant un find et récupérant le user_id du payload
            $user = $usersRepository->find($payload['user_id']);

            // On vérifie qu'on a bien un user et qu'il n'est pas déjà activé
            if ($user && !$user->isVerified()) {
                $user->setIsVerified(true);
                $em->flush(); //function qui synchronise les infos avec la bdd

                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_main');
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
