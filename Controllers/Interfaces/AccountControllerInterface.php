<?php

namespace Controllers\Interfaces;

interface AccountControllerInterface
{


    public function index();


    public function dataInput();


    public function editAvatar();


    public function editEmail($tokenJWT);

    public function editPassword();

    public function editAbout();

    public function deleteAccount();
}