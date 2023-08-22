<section class="sectionDisplaySearch">
    <div>

    </div>
    <!--    <div class="filAriane">-->
    <!--        <a href="index.php">Accueil</a> <i class="fa-solid fa-caret-right"></i>-->
    <!--        <a href="?controller=category&action=index&parentCatID=">wdtgdthgd</a>-->
    <!--        <i class="fa-solid fa-caret-right"></i>-->
    <!--        <a href="?controller=topics&action=list&catID=">srthrth</a>-->
    <!--        <i class="fa-solid fa-caret-right"></i>-->
    <!--    </div>-->
    <div>
        <h1>Résultat de recherche pour : '<?= $key; ?>'</h1>
    </div>
    <div>
        <?php foreach ($result as $key) : ?>
            <div class="blocResultat">
                <div class="blocResultatHeader">
                    <a href="?controller=topics&action=thread&threadID=<?= $key->topicID; ?>#<?= $key->messageID; ?>">
                        <h2><?= $key->topicTitle; ?></h2></a>
                    <h4><?= $key->categoryName; ?></h4>
                </div>
                <div class="blocResultatBody">
                    <p><?= $key->messageText; ?></p>
                </div>
                <div class="blocResultatFooter">
                    <div class="totalMessage"><span></span><span><?= $key->totalMessages; ?></span></div>
                    <div class="nbrVues"><span></span><span><?= $key->views; ?></span></div>
                    <div class="author"><span>Par : <?= $key->pseudo; ?></span></div>
                    <div class="dateMessage">
                        <span>le <?= \Controllers\Services\Toolbox::convertDate($key->messageDate); ?> </span></div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>


    <?php if ($paginator) : ?>
        <div class="paginator">
            <?php if ($numPage > 1) : ?>
                <div>
                    <a href="<?= $baseUri . $numPage - 1 ?>">
                        <button><i class="fa-solid fa-chevron-left"></i></button>
                    </a>
                </div>
            <?php endif; ?>
            <?php
//            dump($numPage);
//            dump($targetPage);
            ?>
            <div>
                <?php for ($i = 0; $i < $nbrLinksPaginator; $i++): ?>
                    <!--                    Premiere version : -->
                    <!--                --><?php //if ($numPage == $nombrePageTotal): ?>
                    <!--                <a href="--><?php //=$baseUri.$numPage+($i-2) ?><!--"><button>--><?php //= $numPage+($i-2)?><!--</button></a>-->
                    <!--                --><?php //else :?>
                    <!--                <a href="--><?php //=$baseUri.($numPage > 1 ? $numPage+($i-1):$numPage+$i);?><!--"><button>--><?php //= $numPage > 1 ? $numPage+($i-1):$numPage+$i ?><!--</button></a>-->
                    <!--                --><?php //endif;?>

                    <!--                Deuxième version :-->

                    <a href="<?= $baseUri . $numPage + $i + $targetPage ?>">
                        <button class="<?= $numPage + $i + $targetPage === $numPage ? "btnPaginatorActive" : "" ?>"><?= $numPage + $i + $targetPage ?></button>
                    </a>
                <?php endfor; ?>

            </div>
            <?php if ($numPage < $nombrePageTotal) : ?>
                <div>
                    <a href="<?= $baseUri . $numPage + 1 ?>">
                        <button><i class="fa-solid fa-chevron-right"></i></button>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</section>