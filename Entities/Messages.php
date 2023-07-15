<?php

namespace Entities;

class Messages
{
    private $messageID;
    private $messageText;
    private $messageDate;
    private $userID;
    private $topicID;


    /**
     * Get the value of messageID
     */
    public function getMessageID()
    {
        return $this->messageID;
    }

    /**
     * Set the value of messageID
     *
     * @return  self
     */
    public function setMessageID($messageID)
    {
        $this->messageID = $messageID;

        return $this;
    }

    /**
     * Get the value of messageText
     */
    public function getMessageText()
    {
        return $this->messageText;
    }

    /**
     * Set the value of messageText
     *
     * @return  self
     */
    public function setMessageText($messageText)
    {
        $this->messageText = $messageText;

        return $this;
    }

    /**
     * Get the value of messageDate
     */
    public function getMessageDate()
    {
        return $this->messageDate;
    }

    /**
     * Set the value of messageDate
     *
     * @return  self
     */
    public function setMessageDate($messageDate)
    {
        $this->messageDate = $messageDate;

        return $this;
    }

    /**
     * Get the value of userID
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * Set the value of userID
     *
     * @return  self
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;

        return $this;
    }

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
}
