<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use App\Repository\UserRepository;
use App\Services\TokenService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('api/user', name: 'user_')]
class UserController extends AbstractController
{
    /**
     * Permet à un user de s'enregistrer
     * Créer un token et l'associe à un user
     * Envoi un mail de confirmation par email à l'utilisateur avec le token poour qu'il puisse confirmer email et activer son compte
     */
    #[Route('/registration', name: 'registration', methods: "POST")]
    public function registration(EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHash, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, TokenService $token): JsonResponse
    {
        $datas = $request->getContent();
        try {
            $user = $serializer->deserialize($datas, User::class, 'json');

            $hash = $passwordHash->hashPassword($user, $user->getPassword());

            $user->setPassword($hash)
                ->setCreatedAt(new \DateTime())
                ->setActived(false);

            $errors = $validator->validate($user);

            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }

            $manager->persist($user);
            $manager->flush();

            $tokenGenerate = $token->create($user, $tokenGenerator, $manager, 'P1D');

            $message = (new TemplatedEmail())
                ->from('no-reply@wiseaty.com')
                ->to($user->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Validation de votre compte')
                ->htmlTemplate('email/confirmAccount.html.twig')
                ->context([
                    'user' => $user->getFirstname(),
                    'token' => $tokenGenerate
                ]);

            $mailer->send($message);

        } catch (RuntimeException $e) {
            return $this->json([
                'statut' => 400,
                'message' => $e->getMessage()
            ], 400);
        }

        return $this->json([
            "message" => "Votre compte à bien été créé"
        ], 201);
    }

    /**
     * Active le compte d'un user et supprime le token qui lui est associé
     * Action déclancher quand l'utilisateur clique sur le lien de confirmation reçu par email
     */
    #[Route('/activation/{token}', name: 'activation', methods: "GET")]
    public function activation($token, UserRepository $user, TokenRepository $tokens, EntityManagerInterface $manager, TokenService $tokenService): JsonResponse
    {
        $token = $tokens->findOneBy(['token' => $token]);
        if (!$token) {
            return $this->json([
                "error" => "Le jeton d'activation n'existe pas"
            ], 400);
        }

        $user = $user->findOneBy(['id' => $token->getUser()]);
        $user->setActived(true);
        $manager->flush();

        $token = $tokenService->delete($token, $manager);
        if (!$token) {
            return $this->json([
                "error" => "Le jeton d'activation à expiré"
            ], 400);
        }

        return $this->json([], 200);
    }

    /**
     * Reçois la demande de mot de passe oublier
     * Créer un token et l'associe à l'utilisateur
     * Envoi un email avec un lien de modification de mot de passe contenant le token pour modifier le mot de passe
     */
    #[Route('/forgotPassword', name: 'forgotPassword', methods: "POST")]
    public function forgotPassword(Request $request, EntityManagerInterface $manager, UserRepository $user, SerializerInterface $serializer, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, TokenService $token): JsonResponse
    {

        $datas = $request->getContent();
        try {
            $user = $user->findOneBy(['email' => $serializer->deserialize($datas, User::class, 'json')->getEmail()]);

            if (!$user || !$user->getActived()) {
                return $this->json([
                    "error" => "Votre compte n'est pas active ou le mail n'existe pas"
                ]);
            }
            $tokenGenerate = $token->create($user, $tokenGenerator, $manager, "PT15M");

            $message = (new TemplatedEmail())
                ->from('no-reply@wiseaty.com')
                ->to($user->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Validation de votre compte')
                ->htmlTemplate('email/confirmAccount.html.twig')
                ->context([
                    'user' => $user->getFirstname(),
                    'token' => $tokenGenerate
                ]);

            $mailer->send($message);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'statut' => 400,
                'message' => $e->getMessage()
            ], 404);
        }

        return $this->json([
            "message" => "Un lien de réinitialisation de mot de passe vous a été envoyé dans votre boite mail."
        ], 201);
    }

    /**
     * Verifie si le token existe ou si le token n'est pas expiré
     * Déclencher par le clique du lien reçu par email lors de la demande de réinitialisation de mot de passe
     * Supprime le token et créer un nouveau token pour permmettre à l'utilisateur d'acceder à la modification de son mot de passe
     */
    #[Route('/forgotPassword/{token}', name: 'forgotPassword_tokenVerify', methods: "GET")]
    public function updatePasswordTokenVerify(Token $token, EntityManagerInterface $manager, TokenService $tokenService, TokenGeneratorInterface $tokenGenerator): JsonResponse
    {
        if (!$tokenService->findOne($token)) {
            return $this->json([
                "error" => "Le jeton n'existe pas ou à expiré"
            ]);
        }

        $user = $token->getUser();

        $tokenService->delete($token, $manager);

        return $this->json([
            "token" => $tokenService->create($user, $tokenGenerator, $manager, 'PT15M')
        ]);
    }

    /**
     * Modifie le mot de passe suit à une demande lors d'un mote de passe oublié ou lors d'une demande de modification de mot de passe en passant par le profile de l'utilisateur
     * Soit il reçois le token et le nouveau mot de passe par le biais du mot de passe oublié soit il recois l'email, l'ancien et le nouveau mot de passe de l'utilisateur
     * Supprime le token si déclancher par le mot de passe oublié
     */
    #[Route('/updatePassword/{token?}', name: 'updatePassword', methods: "PUT")]
    public function updatePassword(Token $token, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHash, UserRepository $userRepository, SerializerInterface $serializer, TokenService $tokenService): JsonResponse
    {
        $datas = $request->getContent();

        try {
            $dataUser = $serializer->deserialize($datas, User::class, 'json');

            if (!$token && !$dataUser->getEmail()) {
                return $this->json([
                    "error" => "Une erreur est survenue"
                ], 400);
            }

            $user = "";

            if ($token) {
                $user = $token->getUser();
                $tokenService->delete($token, $manager);
            } elseif ($userRepository->findOneBy(['email' => $dataUser->getEmail])) {
                $user = $userRepository->findOneBy(['email' => $dataUser->getEmail]);
            }

            if (!$passwordHash->isPasswordValid($user, $dataUser->getOldPassword())) {
                return $this->json([
                    'error' => "Votre mot de passe est incorrect"

                ], 400);
            }

            $hash = $passwordHash->hashPassword($user, $dataUser->getPassword());

            $user->setPassword($hash)
                ->setUpdatedAt(new DateTime());

            $manager->flush();
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'statut' => 400,
                'message' => $e->getMessage()
            ], 404);
        }

        return $this->json([
            "succes" => "votre mot de passe à bien été modifier"
        ]);
    }
}
