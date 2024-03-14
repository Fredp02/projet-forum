<?php
namespace Controllers\Interfaces;
interface DashboardControllerInterface
{
    public function index();

    public function categoriesListShow();

    public function userListShow();

    public function statisticsShow();

    public function categoryAdd();

    public function categoryEdit(string $id);

    public function categoryDelete($id);


}
