<?php

namespace Entities;

class Roles
{
    private $roleId;
    private $roleName;



    /**
     * Get the value of roleId
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set the value of roleId
     *
     * @return  self
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get the value of roleName
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * Set the value of roleName
     *
     * @return  self
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }
}
