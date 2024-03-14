<?php

namespace Entities;

class Categorys
{
    private $categoryId;
    private $categoryName;
    private $categoryIdParent;
    private ?string $categoryDescription;

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
    public function setCategoryId($categoryId): static
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
    public function setCategoryName($categoryName): static
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
    public function setCategoryIdParent($categoryIdParent): static
    {
        $this->categoryIdParent = $categoryIdParent;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getCategoryDescription(): ?string
    {
        return $this->categoryDescription;
    }

    /**
     * @param ?string $categoryDescription
     */
    public function setCategoryDescription(?string $categoryDescription): void
    {
        $this->categoryDescription = $categoryDescription;
    }

}
