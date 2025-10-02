<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FixturesLoadedTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Create the database schema for tests
        $schemaTool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        // Load fixtures
        $loader = new Loader();
        $loader->addFixture(new AppFixtures());

        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function testUsersAreLoaded(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAll();

        // Assert we have exactly 2 users
        $this->assertCount(2, $users);

        // Find users by email to verify specific data
        $johnDoe = $userRepository->findOneBy(['email' => 'john.doe@example.com']);
        $janeSmith = $userRepository->findOneBy(['email' => 'jane.smith@example.com']);

        // Assert users exist
        $this->assertNotNull($johnDoe);
        $this->assertNotNull($janeSmith);

        // Assert user names are correct
        $this->assertEquals('John Doe', $johnDoe->getName());
        $this->assertEquals('Jane Smith', $janeSmith->getName());
    }

    public function testTasksAreLoaded(): void
    {
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $tasks = $taskRepository->findAll();

        // Assert we have exactly 10 tasks
        $this->assertCount(10, $tasks);

        // Check that some tasks are completed and some are not
        $completedTasks = $taskRepository->findBy(['isCompleted' => true]);
        $pendingTasks = $taskRepository->findBy(['isCompleted' => false]);

        $this->assertCount(2, $completedTasks);
        $this->assertCount(8, $pendingTasks);

        // Verify specific tasks exist
        $setupTask = $taskRepository->findOneBy(['title' => 'Setup development environment']);
        $authTask = $taskRepository->findOneBy(['title' => 'Implement user authentication']);

        $this->assertNotNull($setupTask);
        $this->assertNotNull($authTask);

        // Verify the setup task is completed
        $this->assertTrue($setupTask->isCompleted());

        // Verify the auth task is not completed
        $this->assertFalse($authTask->isCompleted());

        // Verify tasks have descriptions
        $this->assertNotNull($setupTask->getDescription());
        $this->assertNotEmpty($setupTask->getDescription());
    }

    public function testTasksHaveCreators(): void
    {
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $userRepository = $this->entityManager->getRepository(User::class);

        $tasks = $taskRepository->findAll();
        $johnDoe = $userRepository->findOneBy(['email' => 'john.doe@example.com']);
        $janeSmith = $userRepository->findOneBy(['email' => 'jane.smith@example.com']);

        // Assert all tasks have creators
        foreach ($tasks as $task) {
            $this->assertNotNull($task->getCreatedBy());
            $this->assertInstanceOf(User::class, $task->getCreatedBy());
        }

        // Assert both users have tasks assigned to them
        $johnTasks = $taskRepository->findBy(['createdBy' => $johnDoe]);
        $janeTasks = $taskRepository->findBy(['createdBy' => $janeSmith]);

        $this->assertGreaterThan(0, count($johnTasks));
        $this->assertGreaterThan(0, count($janeTasks));

        // Assert total tasks match
        $this->assertEquals(10, count($johnTasks) + count($janeTasks));
    }

    public function testTasksHaveCreationDates(): void
    {
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $tasks = $taskRepository->findAll();

        // Assert all tasks have creation dates
        foreach ($tasks as $task) {
            $this->assertNotNull($task->getCreatedAt());
            $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());

            // Ensure creation date is recent (within last hour for this test)
            $oneHourAgo = (new \DateTimeImmutable())->modify('-1 hour');
            $this->assertGreaterThan($oneHourAgo, $task->getCreatedAt());
        }
    }

    public function testUserTaskRelationships(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $johnDoe = $userRepository->findOneBy(['email' => 'john.doe@example.com']);
        $janeSmith = $userRepository->findOneBy(['email' => 'jane.smith@example.com']);

        // Test that users have their tasks collection properly loaded
        $johnTasks = $johnDoe->getTasks();
        $janeTasks = $janeSmith->getTasks();

        $this->assertGreaterThan(0, $johnTasks->count());
        $this->assertGreaterThan(0, $janeTasks->count());

        // Verify that each task in the collection belongs to the correct user
        foreach ($johnTasks as $task) {
            $this->assertEquals($johnDoe, $task->getCreatedBy());
        }

        foreach ($janeTasks as $task) {
            $this->assertEquals($janeSmith, $task->getCreatedBy());
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up
        $this->entityManager->close();
    }
}
