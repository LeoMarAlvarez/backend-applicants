<?php

namespace Osana\Challenge\Http\Controllers;

use Osana\Challenge\Domain\Users\Login;
use Osana\Challenge\Domain\Users\Type;
use Osana\Challenge\Services\Local\LocalUsersRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Osana\Challenge\Services\GitHub\GitHubUsersRepository;

class ShowUserController
{
    /** @var LocalUsersRepository */
    private $localUsersRepository;

    /** @var GitHubUsersRepository */
    private $githubUsersRepository;

    public function __construct(LocalUsersRepository $localUsersRepository, GitHubUsersRepository $githubUsersRepository)
    {
        $this->localUsersRepository = $localUsersRepository;
        $this->githubUsersRepository = $githubUsersRepository;
    }

    public function __invoke(Request $request, Response $response, array $params): Response
    {
        $type = new Type($params['type']);
        $login = new Login($params['login']);

        // TODO: implement me
        if($type->getValue()==='github')
        {
            $user = $this->githubUsersRepository->getByLogin($login);
        }else{
            $user = $this->localUsersRepository->getByLogin($login);
        }

        if($user->getId()->getValue()!='null')
        {
            $user = [
                'id' => $user->getId()->getValue(),
                'login' => $user->getLogin()->getValue(),
                'type' => $user->getType()->getValue(),
                'profile' => [
                    'name' => $user->getProfile()->getName()->getValue(),
                    'company' => $user->getProfile()->getCompany()->getValue(),
                    'location' => $user->getProfile()->getLocation()->getValue(),
                ]
            ];
            $response->getBody()->write(json_encode($user));
        }else{
            $response->getBody()->write(json_encode([
                null
            ]));
        }

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200, 'OK');
    }
}
