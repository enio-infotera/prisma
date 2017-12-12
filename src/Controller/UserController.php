<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * UserController
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * Constructor.
     *
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->userRepository = $container->get(UserRepository::class);
    }

    /**
     * Index
     *
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface The new response
     */
    public function indexAction(Request $request, Response $response): ResponseInterface
    {
        $users = $this->userRepository->findAll();

        $viewData = $this->getViewData([
            'users' => $users
        ]);

        return $this->render($response, 'User/user-index.twig', $viewData);
    }

    /**
     * Edit page
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface The new response
     * @throws Exception
     */
    public function editAction(Request $request, Response $response, $args): ResponseInterface
    {
        $id = $args['id'];

        // Get all GET parameters
        //$query = $request->getQueryParams();

        // Get all POST/JSON parameters
        //$post = $request->getParsedBody();

        // Repository example
        $user = $this->userRepository->getById($id);

        // Insert a new user
        $newUser = new User();
        $newUser->username = 'admin-' . uuid();
        $newUser->disabled = 0;
        $newUserId = $this->userRepository->insert($newUser);

        // Get new new user
        $newUser = $this->userRepository->getById($newUserId);

        // Delete a user
        $this->userRepository->delete($newUser);

        // Get all users
        $users = $this->userRepository->findAll();

        // Session example
        // Increment counter
        $counter = $this->session->get('counter', 0);
        $counter++;
        $this->session->set('counter', $counter);

        // Logger example
        $this->logger->info('My log message');

        // Add data to template
        $viewData = $this->getViewData([
            'id' => $user->id,
            'username' => $user->username,
            'counter' => $counter,
            'users' => $users
        ]);

        // Render template
        return $this->render($response, 'User/user-edit.twig', $viewData);
    }

    /**
     * User review page.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface Response
     */
    public function reviewAction(Request $request, Response $response, $args): ResponseInterface
    {
        $id = $args['id'];

        $response->getBody()->write("Action: Show all reviews of user: $id<br>");
        return $response;
    }
}
