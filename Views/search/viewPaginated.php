<div class="paginator">
    <?php if ($numPage > 1) : ?>
        <div>
            <a href="<?= $dataSearchPaginated['baseUri'] . $numPage - 1 ?>">
                <button><i class="fa-solid fa-chevron-left"></i></button>
            </a>
        </div>
    <?php endif; ?>

    <div>
        <?php for ($i = 0; $i < $dataSearchPaginated['nbrLinksPaginator']; $i++): ?>
            <!--                    Premiere version : -->
            <!--                --><?php //if ($numPage == $dataSearchPaginated['nombrePageTotal'])): ?>
            <!--                <a href="--><?php //=$dataSearchPaginated['baseUri'].$numPage+($i-2) ?><!--"><button>--><?php //= $numPage+($i-2)?><!--</button></a>-->
            <!--                --><?php //else :?>
            <!--                <a href="--><?php //=$dataSearchPaginated['baseUri'].($numPage > 1 ? $numPage+($i-1):$numPage+$i);?><!--"><button>--><?php //= $numPage > 1 ? $numPage+($i-1):$numPage+$i ?><!--</button></a>-->
            <!--                --><?php //endif;?>

            <!--                Deuxième version :-->

            <a href="<?= $dataSearchPaginated['baseUri'] . $numPage + $i + $dataSearchPaginated['targetPage'] ?>">
                <button class="<?= $numPage + $i + $dataSearchPaginated['targetPage'] === $numPage ? "btnPaginatorActive" : "" ?>"><?= $numPage + $i + $dataSearchPaginated['targetPage'] ?></button>
            </a>
        <?php endfor; ?>

    </div>
    <?php if ($numPage < $dataSearchPaginated['nombrePageTotal']) : ?>
        <div>
            <a href="<?= $dataSearchPaginated['baseUri'] . $numPage + 1 ?>">
                <button><i class="fa-solid fa-chevron-right"></i></button>
            </a>
        </div>
    <?php endif; ?>
</div>
