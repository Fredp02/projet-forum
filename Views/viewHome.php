<section>
    <div class="forumListBloc">

        <div class="categorys">
            <!-- c'est un tableau à deux dimension. 1er foreach : depuis le premier niveau. "$category" sera une string, et $sousCategory un array.
                Je peux donc faire un echo de $category mais pas de $sousCategory.
                Pour afficher le contenu de $sousCategory, il faire refaire un boucle dessus, donc deuxième foreach sur le second niveau du tableau d'origine. -->
            <?php
            // dump($allCategorys);
            // dump($groupedCategories);
            // exit;
            ?>
            <?php foreach ($groupedCategories as $category => $sousCategory) : ?>
            <li class="headerCategory">
                <div class="headerCategoryContent">
                    <div class="headerCategoryIcone">
                        <span></span>
                    </div>
                    <div class="categoryNameAndDesc">
                        <h3 class="titreCat"><a
                                href="?controller=category&action=index&parentCatID=<?=$sousCategory[0]['ParentID'];?>"><?= $category; ?></a>
                        </h3>
                    </div>
                    <div class="topicsNumber"><span>Sujets</span></div>
                    <div class="responsesNumber"><span>Messages</span></div>
                    <div class="lastResponse">
                        <span>Dernier message</span>
                    </div>
                </div>
            </li>
            <?php foreach ($sousCategory as $infosSousCat) : ?>
            <li class="subCategorysList">
                <div class="subCategory">
                    <div class="subCategoryIcon">
                        <span></span>
                    </div>
                    <div class="categoryNameAndDesc">

                        <div class="categoryName"><a
                                href="<?= $infosSousCat['url'] ?>"><?= $infosSousCat['name']; ?></a>
                        </div>
                        <div class="categoryDesc"><?= $infosSousCat['description']; ?></div>
                    </div>
                    <div class="topicsNumber"><?= $infosSousCat['totalTopics']; ?></div>
                    <div class="responsesNumber"><?= $infosSousCat['totalMessages']; ?></div>
                    <div class="lastResponse">
                        <div class="lastTopicTitle">
                            <?= $infosSousCat['lastTopicTitle']; ?>
                        </div>
                        <div class="lastActivityUser">
                            par : <?= $infosSousCat['lastMessageUser']; ?>
                        </div>
                        <div class="lastActivityDate">
                            <?= $infosSousCat['lastMessageDate']; ?>
                        </div>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
            <?php endforeach; ?>

        </div>
    </div>
</section>