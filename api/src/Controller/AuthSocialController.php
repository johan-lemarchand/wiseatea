<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\DecodeJWT;
use App\Services\GenerateJWTs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('api', name: 'api_')]
class AuthSocialController extends AbstractController
{
    /**
     * @var ClientRegistry
     */
    private ClientRegistry $clientRegistry;

    /**
     * @var DecodeJWT
     */
    private DecodeJWT $decoder;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * @var GenerateJWTs
     */
    private GenerateJWTs $generator;

    public function __construct(ClientRegistry $clientRegistry, DecodeJWT $decoder, 
        EntityManagerInterface $manager, GenerateJWTs $generator
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->decoder = $decoder;
        $this->manager = $manager;
        $this->generator = $generator;
    }  

    /**
     * Route qui redirige vers le formulaire Google
     */
    #[Route(path: '/connect/google', name: 'connect_google', methods: 'GET')]
    #[Route(path: '/connect/facebook', name: 'connect_facebook', methods: 'GET')]
    public function connectGoogle(Request $request): RedirectResponse
    {
        switch($request->get('_route')) {
            case 'api_connect_google':
                /**
                 * @var GoogleClient $client
                 */
                $client = $this->clientRegistry->getClient('google');
                return $client->redirect();
                break;
            case 'api_connect_facebook':
                /**
                 * @var FacebookClient $client
                 */
                $client = $this->clientRegistry->getClient('facebook');
                return $client->redirect(['public_profile', 'email']);
                break;
        }
        return $this->redirect('/login');
    }

    /**
     * Route de redirection après authentification via Google / Facebook
     * @throws IdentityProviderException
     */
    #[Route(path: '/login/google', name: 'login_google')]
    #[Route(path: '/login/facebook', name: 'login_facebook')]
    public function checkSocialConnect(Request $request, SerializerInterface $serializer, EncoderInterface $encoder): JsonResponse
    {
        $currentRoute = $request->get('_route');

        if ($currentRoute === 'api_login_google') {
            /**
             * Récupère les infos provenant du compte google
             * @var GoogleClient $client
             */
            $client = $this->clientRegistry->getClient('google');

        } elseif ($currentRoute === 'api_login_facebook') {
            /**
             * Récupère les infos provenant du compte facebook
             * @var FacebookClient $client
             */
            $client = $this->clientRegistry->getClient('facebook');
        }

        try {
            $token = $client->getAccessToken(['authorization_code'], [
                'code' => $request->query->get('code')
            ]);
            $userDecode = $client->fetchUserFromToken($token);

        } catch (AccessDeniedException $e) {
            return $this->json([
                "message" => "Erreur : $e"
            ]);
        }

        // Check si le user existe dans la BDD avec son email
        $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $userDecode->getEmail()]);

        // Si le user n'existe pas, on renvoie le googleJwt au front pour que le user valide les conditions avant l'enregistrement en BDD
        if (!$user) {
            $session = $request->getSession();

            // Le user inconnu est enregistré en session
            $session->set('notExistingUser', $userDecode);

            // Récupération du nom du sytème OAuth (facebook, google, ...)
            $social = mb_substr($currentRoute, mb_strripos($currentRoute, '_') + 1);
            // Envoie d'un cookie provisoire pour garder la session du user voulant s'inscrire
            $cookie = new Cookie('PHPSESSID', $session->getId(), strtotime('tomorrow'), __DIR__."/api/registration", true, true);

            $response = $this->json([
                'session_id' => $session->getId(), //@todo A enlever une fois validé
                'message' => "Veuillez renvoyer ces datas à la route suivante : /api/registration/$social, en ajoutant les clés cgu et share_data avec leur valeurs"
            ], 200);
            $response->headers->setCookie($cookie);
            return $response;
        }

        $tokens = $this->generator->generateJWTs($user);

        return new JsonResponse(['token' => $tokens['jwt_token'], 'refresh_token' => $tokens['refresh_token']], 200);
    }

    /**
     * Permet de créer l'utilisateur s'il n'existe pas en bdd
     */
    #[Route(path: '/registration/google', name:'registration_google', methods: 'POST')]
    #[Route(path: '/registration/facebook', name:'registration_facebook', methods: 'POST')]
    public function registrationBySocial(Request $request, UserPasswordHasherInterface $hasher, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        // La requete doit comporter le Bearer token, le cgu et le share_data
        $datas = json_decode($request->getContent(), true);

        $session = $request->getSession();

        // Récupère le notExistingUser en session
        $notExistingUser = $session->get('notExistingUser');

        // Check si notExistingUser est en session et si les conditions sont validées par le front
        if (!$notExistingUser || !$datas['cgu'] || !$datas['share_data']) {
            return $this->json([
                "message" => "Non autorisé"
            ], 403);
        }

        $currentRoute = $request->get('_route');

        try {
            $user = (new User)
                ->setEmail($notExistingUser->getEmail())
                ->setLastname($notExistingUser->getLastName())
                ->setFirstname($notExistingUser->getFirstName())
                ->setCgu($datas['cgu'])
                ->setShareData($datas['share_data'])
                ->setCreatedAt(new \DateTime)
                ->setActived(true);

            if ($currentRoute === 'api_registration_google') {
                $user->setAvatar($notExistingUser->getAvatar())
                     ->setIsGoogle(true);

            } elseif ($currentRoute === 'api_registration_facebook') {
                $user->setAvatar($notExistingUser->getPictureUrl())
                    ->setIsFacebook(true);
            }

            $hash = $hasher->hashPassword($user, bin2hex(random_bytes(10)));
            $user->setPassword($hash);

            $errors = $validator->validate($user);

            if(count($errors) > 0) {
                return $this->json($errors, 400);
            }

            $this->manager->persist($user);
            $this->manager->flush();

        } catch (NotEncodableValueException $e) {
            return $this->json([
                'statut' => 400,
                'message' => $e->getMessage()
            ], 404);
        }

        $tokens = $this->generator->generateJWTs($user);

        // Clear session provisoire
        $session->clear();

        $response = new JsonResponse([
            'message'=> 'Votre compte à été créé',
            'token' => $tokens['jwt_token'],
            'refresh_token' => $tokens['refresh_token']
        ], 201);
        // Clear cookie avec sessionid
        $response->headers->clearCookie('PHPSESSID');
        return $response;
    }
}

