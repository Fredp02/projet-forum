<?php

namespace Controllers\Interfaces;

interface TopicsControllerInterface
{

    /**
     * Affiche la liste des topics
     */
    public function list($catID);

    /**
     * Affiche le contenu d'un topic : son titre est les messages associés
     */
    public function thread($threadID);


    /**
     * Affiche la vue qui permet de créer un topic. Le texte descriptif du premier topic est considéré comme message numéro 1 du topci  , donc géré par les méthodes uploadImage() si besoin et validation().
     */
    public function createTopicView($categoryID);

    /**
     * Crée le titre du topic
     */
    public function createTitleTopic();
}