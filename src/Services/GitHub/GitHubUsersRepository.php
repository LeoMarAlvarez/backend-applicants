<?php

namespace Osana\Challenge\Services\GitHub;

use Osana\Challenge\Domain\Users\Company;
use Osana\Challenge\Domain\Users\Id;
use Osana\Challenge\Domain\Users\Location;
use Osana\Challenge\Domain\Users\Login;
use Osana\Challenge\Domain\Users\Name;
use Osana\Challenge\Domain\Users\Profile;
use Osana\Challenge\Domain\Users\Type;
use Osana\Challenge\Domain\Users\User;
use Osana\Challenge\Domain\Users\UsersRepository;
use Tightenco\Collect\Support\Collection;

class GitHubUsersRepository implements UsersRepository
{
    public function findByLogin(Login $name, int $limit = 0): Collection
    {
        // TODO: implement me
        $array = [];
        $username = ($name->getValue()!='')? "/".$name->getValue():'';
        $per_page = ($limit>0)?"?per_page={$limit}":"";
        $response = $this->request("users{$username}{$per_page}","GET");
        if($name->getValue()==''){
            foreach($response as $object)
            {
                $profile = $this->request('users/'.$object->login,'GET');
                $id = new Id($object->id);
                $login = new Login($object->login);
                $type = new Type('github');
                $companyProfile = new Company(($profile->company)?:"");
                $locationProfile = new Location(($profile->location)?:"");
                $nameProfile = new Name(($profile->name)?:"");
                $profile = new Profile($nameProfile, $companyProfile, $locationProfile);
                $user = new User($id, $login, $type, $profile);
                array_push($array, $user);
            }
        }else if(!isset($response->message)){
            $id = new Id($response->id);
            $login = new Login($response->login);
            $type = new Type('github');
            $companyProfile = new Company(($response->company)?:"");
            $locationProfile = new Location(($response->location)?:"");
            $nameProfile = new Name(($response->name)?:"");
            $profile = new Profile($nameProfile, $companyProfile, $locationProfile);
            $user = new User($id, $login, $type, $profile);
            array_push($array, $user);
        }
        
        return new Collection($array);
    }

    public function getByLogin(Login $name, int $limit = 0): User
    {
        $response = $this->request("users/{$name->getValue()}","GET");
        if(isset($response->message))
        {
            $user = new User(new Id('null'), new Login('null'), new Type('github'), new Profile(new Name('null'), new Company('null'), new Location('null')));
        }else{
            $id = new Id($response->id);
            $login = new Login($response->login);
            $type = new Type('github');
            $companyProfile = new Company(($response->company)?:"");
            $locationProfile = new Location(($response->location)?:"");
            $nameProfile = new Name(($response->name)?:"");
            $profile = new Profile($nameProfile, $companyProfile, $locationProfile);
            $user = new User($id, $login, $type, $profile);
        }
        
        return $user;
    }

    public function add(User $user): void
    {
        throw new OperationNotAllowedException();
    }

    private function request($url, $type, $data=[])
    {
        $peticion = curl_init();
        curl_setopt($peticion, CURLOPT_URL, env('GITHUB_API').$url);
        curl_setopt($peticion, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($peticion, CURLOPT_HTTPHEADER, [
            "Accept: application/vnd.github.v3+json",
            "user-agent: php",
            "Authorization: token ".env('GITHUB_TOKEN')
        ]);
        curl_setopt($peticion, CURLOPT_POST, ($type==='POST')? 1:0);
        curl_setopt($peticion, CURLOPT_POSTFIELDS, $data);
        curl_setopt($peticion, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($peticion);
        curl_close($peticion);
        return json_decode($response);
    }
}
