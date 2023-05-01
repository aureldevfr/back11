<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users/find", name="get_users", methods={"GET"})
     */
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'roles' => $user->getRoles(),
                // Add any other user fields you want to return here
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/users/find/{id}", name="get_user_by_id", methods={"GET"})
     */
    public function getUserById(UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id ' . $id
            );
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'roles' => $user->getRoles(),
            // Add any other user fields you want to return here
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/users/{id}", name="update_user", methods={"PUT"})
     */
    public function updateUser(
        int $id,
        UserRepository $userRepository,
        Request $request,
        Security $security
    ): JsonResponse{
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $currentUser = $security->getUser();

        if (!$currentUser) {
            return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (
            !$currentUser->hasRole('ROLE_ADMIN') &&
            $currentUser->getId() !== $user->getId()
        ) {
            return new JsonResponse(
                ['error' => 'You can only update your own information'],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        // Parse the request body to get the updated user information
        $data = json_decode($request->getContent(), true);

        // Update the user's information and save the changes
        $user->setEmail($data['email']);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['success' => 'User information updated'], JsonResponse::HTTP_OK);
    }
}
