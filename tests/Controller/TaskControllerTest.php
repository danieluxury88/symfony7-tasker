<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $taskRepository;
    private EntityRepository $userRepository;
    private string $path = '/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->taskRepository = $this->manager->getRepository(Task::class);
        $this->userRepository = $this->manager->getRepository(User::class);

        // Create the database schema for tests
        $schemaTool = new SchemaTool($this->manager);
        $classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        // Load fixtures
        $loader = new Loader();
        $loader->addFixture(new AppFixtures());

        $purger = new ORMPurger($this->manager);
        $executor = new ORMExecutor($this->manager, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Tasker');

        // Check that we have 10 tasks from fixtures
        $this->assertCount(10, $this->taskRepository->findAll());
    }

    public function testNew(): void
    {
        $originalCount = $this->taskRepository->count([]);
        $user = $this->userRepository->findOneBy(['email' => 'john.doe@example.com']);

        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Create Task', [
            'task[title]' => 'New Test Task',
            'task[description]' => 'This is a test task description',
            'task[isCompleted]' => false,
            'task[createdBy]' => $user->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame($originalCount + 1, $this->taskRepository->count([]));
    }

    public function testShow(): void
    {
        // Use an existing task from fixtures
        $task = $this->taskRepository->findOneBy(['title' => 'Setup development environment']);
        $this->assertNotNull($task);

        $this->client->request('GET', sprintf('%s%s', $this->path, $task->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Task');

        // Check that the task title is displayed
        self::assertSelectorTextContains('body', 'Setup development environment');
    }

    public function testEdit(): void
    {
        // Use an existing task from fixtures
        $task = $this->taskRepository->findOneBy(['title' => 'Setup development environment']);
        $user = $this->userRepository->findOneBy(['email' => 'jane.smith@example.com']);
        $this->assertNotNull($task);
        $this->assertNotNull($user);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $task->getId()));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Update Task', [
            'task[title]' => 'Updated Task Title',
            'task[description]' => 'Updated task description',
            'task[isCompleted]' => true,
            'task[createdBy]' => $user->getId(),
        ]);

        self::assertResponseRedirects($this->path);

        // Get the updated task from database
        $updatedTask = $this->taskRepository->find($task->getId());
        $this->assertNotNull($updatedTask);

        self::assertSame('Updated Task Title', $updatedTask->getTitle());
        self::assertSame('Updated task description', $updatedTask->getDescription());
        self::assertTrue($updatedTask->isCompleted());
        self::assertSame($user->getId(), $updatedTask->getCreatedBy()->getId());
    }

    public function testRemove(): void
    {
        // Use an existing task from fixtures
        $task = $this->taskRepository->findOneBy(['title' => 'Setup development environment']);
        $this->assertNotNull($task);
        $originalCount = $this->taskRepository->count([]);

        // The delete action requires a POST request with CSRF token
        $this->client->request('GET', sprintf('%s%s', $this->path, $task->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects($this->path);
        self::assertSame($originalCount - 1, $this->taskRepository->count([]));

        // Verify the task is actually deleted
        $deletedTask = $this->taskRepository->find($task->getId());
        self::assertNull($deletedTask);
    }

    public function testDeleteAll(): void
    {
        // Load fixtures first
        $loader = new Loader();
        $loader->addFixture(new AppFixtures());
        $purger = new ORMPurger($this->manager);
        $executor = new ORMExecutor($this->manager, $purger);
        $executor->execute($loader->getFixtures());

        // Get the count of tasks before deletion
        $originalCount = $this->taskRepository->count([]);
        self::assertGreaterThan(0, $originalCount);

        // Submit the delete all form with CSRF token
        $crawler = $this->client->request('GET', $this->path);
        $form = $crawler->selectButton('Delete All Tasks')->form();
        $this->client->submit($form);

        self::assertResponseRedirects($this->path);

        // Follow the redirect to check flash message
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'All tasks have been deleted successfully');

        // Verify all tasks were deleted
        $newCount = $this->taskRepository->count([]);
        self::assertSame(0, $newCount);
    }

    public function testNewWithInvalidData(): void
    {
        $loader = new Loader();
        $loader->addFixture(new AppFixtures());
        $purger = new ORMPurger($this->manager);
        $executor = new ORMExecutor($this->manager, $purger);
        $executor->execute($loader->getFixtures());
        $user = $this->userRepository->findOneBy(['email' => 'john.doe@example.com']);

        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        // Test with empty title (should fail validation)
        $this->client->submitForm('Create Task', [
            'task[title]' => '', // Empty title should fail
            'task[description]' => 'This is a test task description',
            'task[isCompleted]' => false,
            'task[createdBy]' => $user->getId(),
        ]);

        // Should not redirect (form has errors)
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('ul li', 'Task title cannot be empty'); // Should have validation errors
    }

    public function testNewWithEmptyTitle(): void
    {
        $loader = new Loader();
        $loader->addFixture(new AppFixtures());
        $purger = new ORMPurger($this->manager);
        $executor = new ORMExecutor($this->manager, $purger);
        $executor->execute($loader->getFixtures());
        $user = $this->userRepository->findOneBy(['email' => 'john.doe@example.com']);

        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        // Test with empty title (should fail validation)
        $this->client->submitForm('Create Task', [
            'task[title]' => '', // Empty title should fail
            'task[description]' => 'This is a test task description',
            'task[isCompleted]' => false,
            'task[createdBy]' => $user->getId(),
        ]);

        // Should not redirect (form has errors)
        self::assertResponseStatusCodeSame(422);
    }

    public function testEditFormDisplaysUpdateButton(): void
    {
        $task = $this->taskRepository->findOneBy(['title' => 'Setup development environment']);
        $this->assertNotNull($task);

        $crawler = $this->client->request('GET', sprintf('%s%s/edit', $this->path, $task->getId()));
        self::assertResponseStatusCodeSame(200);

        // Check that the Update Task button is present and not disabled
        self::assertSelectorExists('button[type="submit"]');
        self::assertSelectorTextContains('button[type="submit"]', 'Update Task');

        // The button should have proper Bootstrap styling
        self::assertSelectorExists('button.btn-primary');

        // Form should be populated with existing task data
        self::assertInputValueSame('task[title]', $task->getTitle());
        // Description is a textarea, so we check it differently
        self::assertSelectorTextContains('textarea[name="task[description]"]', $task->getDescription());
    }
}
