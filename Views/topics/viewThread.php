<?php

use Controllers\Services\Toolbox;
?>
<section>

    <div class="headerPageTopic">
        <div class="filAriane">
            <a href="index.php">Accueil</a> <i class="fa-solid fa-caret-right"></i>
            <a href="?controller=category&action=index&parentCatID=<?= $infosTopic->parentCategoryID; ?>"><?= $infosTopic->parentCategoryName; ?></a>
            <i class="fa-solid fa-caret-right"></i>
            <a href="?controller=topics&action=list&catID=<?= $infosTopic->categoryID; ?>"><?= $infosTopic->categoryName; ?></a>
            <i class="fa-solid fa-caret-right"></i>
        </div>

        <h1><?= $infosTopic->topicTitle; ?></h1>
    </div>
    <div class="contentTopic">
        <!-- <?php dump($messagesTopics); ?> -->
        <div class="messageList">

            <?php foreach ($messagesTopics as $message) : ?>
                <div class="messageBloc">
                    <div class="userInfos">
                        <div class="avatar"><img src="./images/profils/<?= $message->userID . '/'; ?><?= $message->avatar; ?>" alt="photo de profil de l'utilisateur"></div>
                        <div class="pseudo">
                            <span><?= html_entity_decode($message->pseudo); ?></span>
                        </div>
                        <div class="guitare">
                            <span>
                                <?php if ($message->pseudo !== 'Utilisateur') : ?>
                                    Ma guitare : <?= html_entity_decode($message->guitare); ?>
                                <?php endif; ?>
                            </span>

                        </div>
                        <div class="totalMessagesUser">
                            <span>
                                <?php if ($message->pseudo !== 'Utilisateur') : ?>
                                    <?= $message->totalUserMessages; ?>
                                    message<?= $message->totalUserMessages > 1 ? 's' : ''; ?>
                                <?php endif; ?>
                            </span>
                        </div>

                    </div>
                    <div class="userMessage">
                        <div class="headerMessage">
                            <div class="messageDate">
                                <span><?= Toolbox::convertDate($message->messageDate); ?></span>
                            </div>
                            <div class="iconThread">

                                <?php if ($message->userID === $userID) : ?>
                                    <div class="editMessage">
                                        <a href="?controller=message&action=viewEdit&messageID=<?= $message->messageID; ?>"><button><i class="fa-solid fa-pen"></i></i></button></a>

                                    </div>
                                <?php endif; ?>

                                <div class="quoteMessage" data-pseudo="<?= $message->pseudo; ?>" data-date="<?= Toolbox::convertDate($message->messageDate); ?>">
                                    <button><i class="fa-solid fa-quote-right"></i></button>
                                </div>
                            </div>

                        </div>

                        <div class="messageText"><span><?= $message->messageText; ?></span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="alertMessageTopic"></div>
        <div class="divReponse">
            <form class="formMessage">
                <input name="inputMessage" class="inputMessage" type="hidden">
                <input name="action" type="hidden" value="create">
                <input name="targetID" class="targetID" type="hidden" value="<?= $infosTopic->topicID; ?>">
                <input name="tokenCSRF" class="tokenCSRF" type="hidden" value="<?= $_SESSION['tokenCSRF']; ?>">
                <div class="editor">
                </div>

                <button type="submit" class="btnEnvoyer">Envoyer</button>
            </form>
        </div>

        <template class="template-item">
            <div class="messageBloc">
                <div class="userInfos">
                    <div class="avatar"><img src="" alt="photo de profil de l'utilisateur"></div>
                    <div class="pseudo"><span></span></div>
                    <div class="guitare"><span></span></div>
                    <div class="totalMessagesUser"><span></span></div>
                </div>
                <div class="userMessage">
                    <div class="headerMessage">
                        <div class="messageDate"><span></span></div>
                        <div class="iconThread">
                            <div class="editMessage">
                                <a href=""><button><i class="fa-solid fa-pen"></i></i></button></a>
                            </div>
                            <div class="quoteMessage" data-pseudo="" data-date="">
                                <button><i class="fa-solid fa-quote-right"></i></button>
                            </div>
                        </div>

                    </div>
                    <div class="messageText"><span></span></div>
                </div>
            </div>
        </template>
    </div>
    <div class="filAriane">
        <a href="index.php">Accueil</a> <i class="fa-solid fa-caret-right"></i>
        <a href="?controller=category&action=index&parentCatID=<?= $infosTopic->parentCategoryID; ?>"><?= $infosTopic->parentCategoryName; ?></a>
        <i class="fa-solid fa-caret-right"></i>
        <a href="?controller=topics&action=list&catID=<?= $infosTopic->categoryID; ?>"><?= $infosTopic->categoryName; ?></a>
        <i class="fa-solid fa-caret-right"></i>
    </div>




</section>