<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create two users
        $user1 = new User();
        $user1->setName('John Doe');
        $user1->setEmail('john.doe@example.com');
        $manager->persist($user1);

        $user2 = new User();
        $user2->setName('Jane Smith');
        $user2->setEmail('jane.smith@example.com');
        $manager->persist($user2);

        // Create 10 tasks
        $tasks = [
            [
                'title' => 'Setup development environment',
                'description' => 'Install required tools and configure the development environment for the new project.',
                'isCompleted' => true,
                'createdBy' => $user1
            ],
            [
                'title' => 'Design database schema',
                'description' => 'Create entity relationship diagrams and define the database structure.',
                'isCompleted' => true,
                'createdBy' => $user1
            ],
            [
                'title' => 'Implement user authentication',
                'description' => 'Create login and registration functionality with proper security measures.',
                'isCompleted' => false,
                'createdBy' => $user2
            ],
            [
                'title' => 'Create task management features',
                'description' => 'Implement CRUD operations for task management including create, read, update, and delete.',
                'isCompleted' => false,
                'createdBy' => $user1
            ],
            [
                'title' => 'Write unit tests',
                'description' => 'Create comprehensive unit tests for all application components.',
                'isCompleted' => false,
                'createdBy' => $user2
            ],
            [
                'title' => 'Setup CI/CD pipeline',
                'description' => 'Configure continuous integration and deployment pipeline for automated testing and deployment.',
                'isCompleted' => false,
                'createdBy' => $user1
            ],
            [
                'title' => 'Create API documentation',
                'description' => 'Document all API endpoints with proper examples and usage guidelines.',
                'isCompleted' => false,
                'createdBy' => $user2
            ],
            [
                'title' => 'Implement email notifications',
                'description' => 'Add email notification system for task assignments and updates.',
                'isCompleted' => false,
                'createdBy' => $user1
            ],
            [
                'title' => 'Add search functionality',
                'description' => 'Implement search and filtering capabilities for tasks and users.',
                'isCompleted' => false,
                'createdBy' => $user2
            ],
            [
                'title' => 'Performance optimization',
                'description' => 'Optimize database queries and improve application performance.',
                'isCompleted' => false,
                'createdBy' => $user1
            ]
        ];

        foreach ($tasks as $taskData) {
            $task = new Task();
            $task->setTitle($taskData['title']);
            $task->setDescription($taskData['description']);
            $task->setIsCompleted($taskData['isCompleted']);
            $task->setCreatedBy($taskData['createdBy']);
            $task->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($task);
        }

        $manager->flush();
    }
}
