<?php

use Controllers\Services\Toolbox;

// dump($categoryID);
?>
<section>
    <div class="forumListBloc">
        <div class="topformList">
            <div id="filAriane"><a href="<?= URL; ?>">Guitare Forum </a> > <a
                    href="<?= URL . 'home/' . $categorySlug . '.' . $categoryID ?>"><?= $categoryName; ?></a></div>
            <div class="divBtnCreateTopic">
                <a href="<?= URL; ?>createTopic/<?= $categoryID; ?>">
                    <button class="btnCreateTopic">Créer</button>
                </a>
            </div>
        </div>


        <div class="categorys">
            <!-- header -->
            <li class="headerCategory">
                <div class="headerCategoryContent">
                    <div class="headerCategoryIcone">
                        <span></span>
                    </div>
                    <div class="infosTopic">
                        <h3 class="titreCat"><?= $categoryName; ?></h3>
                    </div>
                    <div class="responsesNumber"><span>Réponses</span></div>
                    <div class="viewNumber"><span>Vues</span></div>
                    <div class="lastResponse">
                        <span>Dernier message</span>
                    </div>
                </div>
            </li>
            <!-- foreach -->
            <?php foreach ($listTopics as $topic) : ?>
            <li class="subCategorysList">
                <div class="subCategory">
                    <div class="subCategoryIcon">
                        <span></span>
                    </div>
                    <div class="infosTopic">
                        <!-- Je profite du foreach de cette page pour construire les urls de chaque topics -->
                        <?php $topicUrl =  URL . 'topic/' . $topic->topicSlug . '.' . $topic->topicID; ?>
                        <div class="topicName"><a href="<?= $topicUrl; ?>"><?= $topic->topicTitle; ?></a>
                        </div>
                        <div class="topicAuthor"><?= $topic->topicCreator; ?> -
                            <?= Toolbox::convertDate($topic->topicDate, 'd MMMM Y'); ?> </div>
                    </div>
                    <div class="responsesNumber"><?= $topic->totalMessages - 1; ?></div>
                    <div class="viewNumber">215</div>
                    <div class="lastResponse">

                        <div class="lastActivityDate">
                            <span><?= Toolbox::convertDate($topic->latestMessageDate, 'd MMMM Y'); ?></span>
                        </div>
                        <div class="lastActivityUser">
                            <span>Par : <?= $topic->latestMessageUser; ?></span>
                        </div>


                    </div>
                </div>
            </li>
            <?php endforeach; ?>

        </div>
    </div>
</section>