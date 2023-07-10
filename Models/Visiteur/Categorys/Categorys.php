<?php

namespace Models\Visiteur\Categorys;

class Categorys
{
    private $categoryId;
    private $categoryName;
    private $categoryIdParent;

    /**
     * Get the value of categoryId
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set the value of categoryId
     *
     * @return  self
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get the value of categoryName
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Set the value of categoryName
     *
     * @return  self
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Get the value of categoryIdParent
     */
    public function getCategoryIdParent()
    {
        return $this->categoryIdParent;
    }

    /**
     * Set the value of categoryIdParent
     *
     * @return  self
     */
    public function setCategoryIdParent($categoryIdParent)
    {
        $this->categoryIdParent = $categoryIdParent;

        return $this;
    }
}
