<?php

use Controllers\Services\Toolbox;
?>
<section>

    <div class="forumListBloc">
        <div class="filAriane">
            <a href="index.php">Accueil</a> <i class="fa-solid fa-caret-right"></i>

        </div>
        <div class="categorys">
            <?php
            // dump($getDetailsParentCat);
            // exit;
            ?>

            <li class="headerCategory">
                <div class="headerCategoryContent">
                    <div class="headerCategoryIcone">
                        <span></span>
                    </div>
                    <div class="categoryNameAndDesc">
                        <h3 class="titreCat"><?= $getDetailsParentCat[0]->parentCategoryName; ?></h3>
                    </div>
                    <div class="topicsNumber"><span>Sujets</span></div>
                    <div class="responsesNumber"><span>Messages</span></div>
                    <div class="lastResponse">
                        <span>Dernier message</span>
                    </div>
                </div>
            </li>
            <?php foreach ($getDetailsParentCat as $data) : ?>
            <li class="subCategorysList">
                <div class="subCategory">
                    <div class="subCategoryIcon">
                        <span></span>
                    </div>
                    <div class="categoryNameAndDesc">

                        <div class="categoryName"><a href="<?= $data->url ?>"><?= $data->name; ?></a>
                        </div>
                        <div class="categoryDesc"><?= $data->description; ?></div>
                    </div>
                    <div class="topicsNumber"><?= $data->totalTopics; ?></div>
                    <div class="responsesNumber"><?= $data->totalMessages; ?></div>
                    <div class="lastResponse">
                        <div class="lastTopicTitle">
                            <?= $data->lastTopicTitle; ?>
                        </div>
                        <div class="lastActivityUser">
                            par : <?= $data->lastMessageUser; ?>
                        </div>
                        <div class="lastActivityDate">
                            <?= Toolbox::convertDate($data->lastMessageDate, 'd MMMM Y'); ?>
                        </div>
                    </div>
                </div>
            </li>

            <?php endforeach; ?>

        </div>

    </div>
</section>