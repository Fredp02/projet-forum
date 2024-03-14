<?php
namespace Controllers\Interfaces;

interface ForgotPassControllerInterface
{
    public function index();

    public function sendEmail();

    public function resetPassView($tokenJWT);

    public function validResetPass($tokenJWT);
}