<?php
// src\Controller\AuthorController.php
namespace App\Controller;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class AuthorController extends AbstractController
{
#[Route('/api/authors', name: 'authors', methods: ['GET'])]
public function getAllAuthor(AuthorRepository $authorRepository,
SerializerInterface $serializer): JsonResponse
{
$authorList = $authorRepository->findAll();
$jsonAuthorList = $serializer->serialize($authorList, 'json',
['groups' => 'getBooks']);
return new JsonResponse($jsonAuthorList, Response::HTTP_OK,
[], true);
}
#[Route('/api/authors/{id}', name: 'detailAuthor', methods:
['GET'])]
public function getAuthor(Author $author, SerializerInterface
$serializer): JsonResponse
{
$jsonAuthor = $serializer->serialize($author, 'json', ['groups'
=> 'getBooks']);
return new JsonResponse($jsonAuthor, Response::HTTP_OK, [],
true);
}
#[Route('/api/authors', name: 'createAuthor', methods: ['POST'])]
public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
{
$author = $serializer->deserialize($request->getContent(), Author::class, 'json');
$errors = $validator->validate($author);
if (count($errors) > 0) {
return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
}
$em->persist($author);
$em->flush();
$jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getBooks']);
return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, [], true);
}
#[Route('/api/authors/{id}', name: 'updateAuthor', methods: ['PUT'])]
public function updateAuthor(Request $request, SerializerInterface $serializer, Author $currentAuthor, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
{
$updatedAuthor = $serializer->deserialize(
$request->getContent(),
Author::class,
'json',
[AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]
);
$errors = $validator->validate($currentAuthor);
if (count($errors) > 0) {
return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
}
$em->flush();
$jsonAuthor = $serializer->serialize($currentAuthor, 'json', ['groups' => 'getBooks']);
return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
}
#[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
public function deleteAuthor(Author $author, EntityManagerInterface $em): JsonResponse
{
$em->remove($author);
$em->flush();
return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}
}



