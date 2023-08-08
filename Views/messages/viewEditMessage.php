<section class="sectionCreateTopic">
    <div class="blocCreateTopic">
        <?php
        // dump($infoMessage);
        ?>
        <div class="filAriane">
            <a href="index.php">Accueil</a> <i class="fa-solid fa-caret-right"></i>
            <a href="?controller=category&action=index&parentCatID=<?= $infoMessage->parentID; ?>"><?= $infoMessage->parentName; ?></a>
            <i class="fa-solid fa-caret-right"></i>
            <a href="?controller=topics&action=list&catID=<?= $infoMessage->categoryID; ?>"><?= $infoMessage->categoryName; ?></a>
            <i class="fa-solid fa-caret-right"></i>
        </div>

        <div class="titleCreateTopic">
            <div>
                <h1>Modification de votre message dans: </h1>
            </div>
            <div>
                <h2><?= $infoMessage->topicTitle; ?></h2>
            </div>


        </div>
        <div class="alertMessageTopic rouge"></div>
        <form class="formMessage">

            <!-- inputMessage : là ou va être injecté le contenur de Quill -->
            <input name="inputMessage" class="inputMessage" type="hidden" value="<?= htmlspecialchars($infoMessage->messageText); ?>">
            <input name="messageID" class="messageID" type="hidden" value="<?= $infoMessage->messageID; ?>">
            <input name="topicID" class="topicID" type="hidden" value="<?= $infoMessage->topicID; ?>">
            <input name="tokenCSRF" class="tokenCSRF" type="hidden" value="<?= $_SESSION['tokenCSRF']; ?>">
            <div class="editor">
            </div>

            <button type="submit" class="btnEditMessage">Modifier</button>
        </form>

    </div>


</section>