<?php

namespace Entities;

class Topics
{
    private $topicID;
    private $topicTitle;
    private $topicDate;
    private $topicCategoryID;
    private $topicUserID;

    /**
     * Get the value of topicID
     */
    public function getTopicID()
    {
        return $this->topicID;
    }

    /**
     * Set the value of topicID
     *
     * @return  self
     */
    public function setTopicID($topicID)
    {
        $this->topicID = $topicID;

        return $this;
    }

    /**
     * Get the value of topicTitle
     */
    public function getTopicTitle()
    {
        return $this->topicTitle;
    }

    /**
     * Set the value of topicTitle
     *
     * @return  self
     */
    public function setTopicTitle($topicTitle)
    {
        $this->topicTitle = $topicTitle;

        return $this;
    }



    /**
     * Get the value of topicDate
     */
    public function getTopicDate()
    {
        return $this->topicDate;
    }

    /**
     * Set the value of topicDate
     *
     * @return  self
     */
    public function setTopicDate($topicDate)
    {
        $this->topicDate = $topicDate;

        return $this;
    }

    /**
     * Get the value of topicCategoryID
     */
    public function getTopicCategoryID()
    {
        return $this->topicCategoryID;
    }

    /**
     * Set the value of topicCategoryID
     *
     * @return  self
     */
    public function setTopicCategoryID($topicCategoryID)
    {
        $this->topicCategoryID = $topicCategoryID;

        return $this;
    }

    /**
     * Get the value of topicUserID
     */
    public function getTopicUserID()
    {
        return $this->topicUserID;
    }

    /**
     * Set the value of topicUserID
     *
     * @return  self
     */
    public function setTopicUserID($topicUserID)
    {
        $this->topicUserID = $topicUserID;

        return $this;
    }
}