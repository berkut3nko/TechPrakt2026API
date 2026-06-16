<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/projects', name: 'api_project_')]
class ProjectController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();
        
        $data = [];
        foreach ($projects as $project) {
            $data[] = [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['status'])) {
            return $this->json(['message' => 'Missing required fields: name, status'], 400);
        }

        $project = new Project();
        $project->setName($data['name']);
        $project->setDescription($data['description'] ?? null);
        $project->setStatus($data['status']);

        $entityManager->persist($project);
        $entityManager->flush();

        return $this->json([
            'id' => $project->getId(),
            'name' => $project->getName(),
            'status' => $project->getStatus()
        ], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);

        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }

        return $this->json([
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'status' => $project->getStatus(),
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);

        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $project->setName($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $project->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $project->setStatus($data['status']);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'status' => $project->getStatus(),
        ]);
    }

    #[Route('/{id}', name: 'destroy', methods: ['DELETE'])]
    public function destroy(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);

        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }

        $entityManager->remove($project);
        $entityManager->flush();

        return $this->json(null, 204);
    }
}