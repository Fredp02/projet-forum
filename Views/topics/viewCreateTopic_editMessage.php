<section class="sectionCreateTopic">
    <div class="blocCreateTopic">
        <?php
        // dump($infoCategory);
        ?>
        <div class="filAriane"><a href="index.php">Accueil</a> <i class="fa-solid fa-caret-right"></i>
            <a href="?controller=category&action=index&parentCatID=<?= $infoCategory->categoryParentID; ?>"><?= $infoCategory->categoryParentName; ?></a>
            <i class="fa-solid fa-caret-right"></i>
        </div>

        <div class="titleCreateTopic">
            <div>
                <h1><?= $title_a; ?></h1>
            </div>
            <div>
                <h2><?= $title_b; ?></h2>
            </div>


        </div>
        <div class="alertCreateTopic rouge"></div>
        <form class="formCreateTopic">
            <?php if ($action === 'createTopic') : ?>
                <div>
                    <label for="titleTopic" class="labelTitleTopic">Titre du topic</label>
                    <input type=text id="titleTopic" name="titleTopic" class="inputTitleCreateTopic">
                    <input id="topicID" name="topicID" class="topicID" type="hidden">
                </div>
            <?php endif; ?>

            <!-- inputResponse : là ou va être injecté le contenur de Quill -->
            <input name="inputResponse" class="inputTextTopic" type="hidden">

            <input name="categoryID" class="categoryID" type="hidden" value="<?= $infoCategory->categoryID; ?>">
            <input name="tokenCSRF" class="tokenCSRF" type="hidden" value="<?= $_SESSION['tokenCSRF']; ?>">
            <div class="editor">
            </div>

            <button type="submit" class="btnCreateTopic"><?= $textAction; ?></button>
        </form>

    </div>


</section>