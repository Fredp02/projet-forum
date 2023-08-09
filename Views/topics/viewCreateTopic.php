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
                <h1>Création d'un topic dans la catégorie : </h1>
            </div>
            <div>
                <h2><?= $infoCategory->categoryName; ?></h2>
            </div>


        </div>
        <div class="alertCreateTopic rouge"></div>
        <form class="formCreateTopic">
            <div>
                <label for="titleTopic" class="labelTitleTopic">Titre du topic</label>
                <input type=text id="titleTopic" name="titleTopic" class="inputTitleCreateTopic">
                <input name="categoryID" type="hidden" value="<?= $infoCategory->categoryID; ?>">
            </div>

            <!-- inputResponse : là ou va être injecté le contenur de Quill -->
            <input name="inputMessage" class="inputMessage" type="hidden">
            <input name="topicID" class="topicID" type="hidden">
            <input name="action" type="hidden" value="createTopic">
            <input name="tokenCSRF" class="tokenCSRF" type="hidden" value="<?= $_SESSION['tokenCSRF']; ?>">
            <div class="editor">
            </div>

            <button type="submit" class="btnCreateTopic">Créer</button>
        </form>

    </div>


</section>