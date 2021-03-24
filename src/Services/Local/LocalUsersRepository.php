<?php

namespace Osana\Challenge\Services\Local;

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

class LocalUsersRepository implements UsersRepository
{
    public function findByLogin(Login $login, int $limit = 0): Collection
    {
        $array = [];
        // TODO: implement me
        $users = fopen("../data/users.csv","r");
        $profiles = fopen("../data/profiles.csv","r");
        $vueltas = 0;
        while(($datos = fgetcsv($users,",")) == true && ($dProfile = fgetcsv($profiles,',')) ==true && ($vueltas<=$limit))
        {
            $id = new Id($datos[0]);
            $newLogin = new Login($datos[1]);
            $type = new Type('local');
            $companyProfile = new Company($dProfile[1]);
            $locationProfile = new Location($dProfile[2]);
            $nameProfile = new Name($dProfile[3]);
            $profile = new Profile($nameProfile, $companyProfile, $locationProfile);
            $user = new User($id, $newLogin, $type, $profile);
            if($datos[1]!='login')
            {
                if($login->getValue()!=''){
                    if(str_starts_with($newLogin->getValue(), $login->getValue()))
                        array_push($array, $user);
                }else{
                    array_push($array, $user);
                }
                if($limit!=0)
                    $vueltas++;
            }
        }
        fclose($users);
        fclose($profiles);
        return new Collection($array);
    }

    public function getByLogin(Login $login, int $limit = 0): User
    {
        // TODO: implement me
        $user = new User(new Id('null'), new Login('null'), new Type('local'), new Profile(new Name('null'),new Company('null'),new Location('null')));
        $users = fopen("../data/users.csv","r");
        $profiles = fopen("../data/profiles.csv","r");
        while(($datos = fgetcsv($users,",")) == true && ($dProfile = fgetcsv($profiles,',')) ==true)
        {
            if($datos[1]!='login')
            {
                if(strcmp($login->getValue(), $datos[1]) === 0)
                {
                    $id = new Id($datos[0]);
                    $newLogin = new Login($datos[1]);
                    $type = new Type('local');
                    $companyProfile = new Company($dProfile[1]);
                    $locationProfile = new Location($dProfile[2]);
                    $nameProfile = new Name($dProfile[3]);
                    $profile = new Profile($nameProfile, $companyProfile, $locationProfile);
                    $user = new User($id, $newLogin, $type, $profile);
                    break;
                }
            }
        }
        fclose($users);
        fclose($profiles);
        return $user;
}

    public function add(User $user): void
    {
        // TODO: implement me
    }
}
