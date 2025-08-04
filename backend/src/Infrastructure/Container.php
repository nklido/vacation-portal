<?php

namespace App\Infrastructure;

use App\Application\Controllers\LoginController;
use App\Application\Controllers\UserController;
use App\Application\Controllers\VacationRequestController;
use App\Application\User\Handler\UserStoreHandler;
use App\Application\User\Handler\UserUpdateHandler;
use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Persistence\MySQLUserRepository;
use App\Infrastructure\Persistence\MySQLVacationRequestRepository;

class Container
{
    private static ?MySQLUserRepository $userRepository = null;
    private static ?MySQLVacationRequestRepository $vacationRequestRepository = null;
    private static ?JwtService $jwtService = null;

    public static function userRepository(): MySQLUserRepository
    {
        if (!self::$userRepository) {
            self::$userRepository = new MySQLUserRepository();
        }
        return self::$userRepository;
    }

    public static function vacationRequestRepository(): MySQLVacationRequestRepository
    {
        if (!self::$vacationRequestRepository) {
            self::$vacationRequestRepository = new MySQLVacationRequestRepository();
        }
        return self::$vacationRequestRepository;
    }

    public static function jwtService(): JwtService
    {
        if (!self::$jwtService) {
            self::$jwtService = new JwtService();
        }
        return self::$jwtService;
    }

    public static function loginController(): LoginController
    {
        return new LoginController(
            self::jwtService(),
            self::userRepository()
        );
    }

    public static function userController(): UserController
    {
        return new UserController(
            self::userRepository(),
            self::userStoreHandler(),
            self::userUpdateHandler()
        );
    }

    public static function vacationRequestController(): VacationRequestController
    {
        return new VacationRequestController(
            self::vacationRequestRepository(),
            self::userRepository()
        );
    }

    public static function userStoreHandler(): UserStoreHandler
    {
        return new UserStoreHandler(
            self::userRepository()
        );
    }

    public static function userUpdateHandler(): UserUpdateHandler
    {
        return new UserUpdateHandler(
            self::userRepository()
        );
    }
}
