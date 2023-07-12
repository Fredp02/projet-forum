<?php


use Controllers\Services\Toolbox; ?>
<section>
    <?php
    // $test = html_entity_decode("&lt;p&gt;");
    // dump($test);
    // dump($infosTopic);
    // dump($messagesTopics);
    ?>
    <div class="headerPageTopic">
        <div id="filAriane"><a href="<?= URL; ?>accueil">Guitare Forum </a> > <a href="<?= URL . 'accueil/' . $infosTopic->categorySlug . '.' . $infosTopic->categoryID; ?>"><?= $infosTopic->categoryName; ?></a>
            > <a href=""><?= $infosTopic->topicTitle; ?></a>
        </div>
        <h1><?= $infosTopic->topicTitle; ?></h1>
    </div>
    <div class="contentTopic">
        <div class="messageList">
            <?php foreach ($messagesTopics as $message) : ?>
                <div class="messageBloc">
                    <div class="userInfos">
                        <div class="avatar"><img src="../images/profils/<?= $message->userID . '/'; ?><?= $message->avatar; ?>" alt="photo de profil de l'utilisateur"></div>
                        <div class="pseudo">
                            <span><?= $message->pseudo; ?></span>
                        </div>
                        <div class="guitare">
                            <span>Ma guitare : <?= $message->guitare; ?></span>
                        </div>
                        <div class="totalMessagesUser">
                            <span><?= $message->totalUserMessages; ?>
                                message<?= $message->totalUserMessages > 1 ? 's' : ''; ?></span>
                        </div>

                    </div>
                    <div class="userMessage">
                        <div class="headerMessage">
                            <div class="messageDate">
                                <span><?= Toolbox::convertDate($message->messageDate); ?></span>
                            </div>
                            <div class="quoteMessage" data-pseudo="<?= $message->pseudo; ?>" data-date="<?= Toolbox::convertDate($message->messageDate); ?>">
                                <button><i class="fa-solid fa-quote-right"></i></button>
                            </div>
                        </div>

                        <!-- Les messages provenant de la base de données ont besoins d'être décodé avant l'affichage, car avant enregistrement, il y a eu un "htmlspecialchars" -->
                        <div class="messageText"><span><?= html_entity_decode($message->messageText); ?></span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="alertMessageTopic"></div>
        <div class="divReponse">
            <form class="formResponse">
                <input name="inputResponse" class="inputResponse" type="hidden">
                <input name="topicID" class="topicID" type="hidden" value="<?= $infosTopic->topicID; ?>">
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
                        <div class="quoteMessage" data-pseudo="" data-date="">
                            <button><i class="fa-solid fa-quote-right"></i></button>
                        </div>
                    </div>
                    <div class="messageText"><span></span></div>
                </div>
            </div>
        </template>
    </div>


    <div>

    </div>

</section>