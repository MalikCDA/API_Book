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
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
class AuthorController extends AbstractController
{
#[Route('/api/authors', name: 'authors', methods: ['GET'])]
public function getAllAuthor(AuthorRepository $authorRepository,
SerializerInterface $serializer, JMSSerializerInterface $jmsSerializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
{
$page = $request->get('page', 1);
$limit = $request->get('limit', 10);
$idCache = "getAllAuthors-{$page}-{$limit}";
$authorList = $cachePool->get($idCache, function (ItemInterface $item) use ($authorRepository, $page, $limit) {
$item->tag("authorsCache");
return $authorRepository->findAllWithPagination($page, $limit);
});
// Use JMS to include HATEOAS and groups
$context = SerializationContext::create()->setGroups(['getBooks']);
return new JsonResponse($jmsSerializer->serialize($authorList, 'json', $context), Response::HTTP_OK, [], true);
}
#[Route('/api/authors/{id}', name: 'detailAuthor', methods:
['GET'], requirements: ['id' => '\\d+'])]
public function getAuthor(Author $author, SerializerInterface
$serializer, JMSSerializerInterface $jmsSerializer): JsonResponse
{
$context = SerializationContext::create()->setGroups(['getBooks']);
return new JsonResponse($jmsSerializer->serialize($author, 'json', $context), Response::HTTP_OK, [], true);
}
#[Route('/api/authors', name: 'createAuthor', methods: ['POST'])]
public function createAuthor(Request $request, SerializerInterface $serializer, JMSSerializerInterface $jmsSerializer, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
{
$author = $serializer->deserialize($request->getContent(), Author::class, 'json');
$errors = $validator->validate($author);
if (count($errors) > 0) {
return $this->json($errors, Response::HTTP_BAD_REQUEST);
}
$em->persist($author);
$em->flush();
$cachePool->invalidateTags(["authorsCache"]);
$context = SerializationContext::create()->setGroups(['getBooks']);
return new JsonResponse($jmsSerializer->serialize($author, 'json', $context), Response::HTTP_CREATED, [], true);
}
#[Route('/api/authors/{id}', name: 'updateAuthor', methods: ['PUT'], requirements: ['id' => '\\d+'])]
public function updateAuthor(Request $request, SerializerInterface $serializer, JMSSerializerInterface $jmsSerializer, Author $currentAuthor, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
{
$updatedAuthor = $serializer->deserialize(
$request->getContent(),
Author::class,
'json',
[AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]
);
$errors = $validator->validate($currentAuthor);
if (count($errors) > 0) {
return $this->json($errors, Response::HTTP_BAD_REQUEST);
}
$em->flush();
$cachePool->invalidateTags(["authorsCache"]);
$context = SerializationContext::create()->setGroups(['getBooks']);
return new JsonResponse($jmsSerializer->serialize($currentAuthor, 'json', $context), Response::HTTP_OK, [], true);
}
#[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
public function deleteAuthor(Author $author, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
{
$em->remove($author);
$em->flush();
$cachePool->invalidateTags(["authorsCache"]);
return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}

#[Route('/api/authors/cache', name: 'clearAuthorsCache', methods: ['DELETE'])]
public function clearAuthorsCache(TagAwareCacheInterface $cachePool): JsonResponse
{
$cachePool->invalidateTags(["authorsCache"]);
return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}
}



