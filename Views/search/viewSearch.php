<section class="blocSearch">
    <?php
    // dump($categorysList);
    ?>
    <div class="blocTitleSearch">
        <h1>Recherche avancée</h1>
    </div>

    <form class="formSearch" action="" method="GET">
        <input type="hidden" name="controller" value="search">
        <input type="hidden" name="action" value="display">
        <div class="keywordsBloc">
            <div>
                <label for="keywords" class="">Mots clés</label>
            </div>
            <div>
                <div>
                    <input type="text" class="" id="keywords" name="key" placeholder="Entrez votre mot clé..." require>
                </div>
                <div class="checkboxBloc">
                    <input type="checkbox" class="" id="checkboxTitleOnly" name="title">
                    <label for="checkboxTitleOnly">Titre seulement</label>
                </div>
            </div>
        </div>
        <div class="postedByBloc">
            <div>
                <label for="author" class="emailLabel">Posté par</label>
            </div>
            <div>
                <input type="text" class="" id="author" name="author" placeholder="Entrez le nom du membre">
            </div>
        </div>
        <div class="dateBloc">
            <div>
                <label class="">Date</label>
            </div>
            <div>
                <div>
                    <label for="from">De</label>
                    <input type="date" name="from" id="from">
                </div>
                <div>
                    <label for="to">A</label>
                    <input type="date" name="to" id="to">
                </div>
            </div>
        </div>
        <div class="selectForumBloc">
            <div>
                <label for="selectForum">Rechercher dans </label>
            </div>
            <div>
                <select name="select[]" id="selectForum" multiple>
                    <option value="all">Tous les forums</option>
                    <?php foreach ($categorysList as $key) : ?>
                        <option value="<?= $key->categoryID . ($key->categoryParentID === null ? '-p' : ''); ?>"><?= $key->categoryParentID !== null ? "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp" : "&bull;&nbsp"; ?><?= $key->categoryName; ?></option>
                    <?php endforeach; ?>

                </select>
                <span>Maintenez "Ctrl" ou "Commande" pour sélectionner plusieurs forums</span>
            </div>
        </div>
        <div class="orderBloc">
            <div>
                <label for="order">Trier par </label>
            </div>
            <div class="orderBlocInput">
                <div>
                    <select name="order" id="order">
                        <option value="date" selected>Date</option>
                        <option value="forum">Forum</option>
                        <option value="title">Titre du topic</option>
                        <option value="author">Auteur</option>

                    </select>
                </div>
                <div>
                    <div>
                        <input type="radio" name="sort" id="asc" value="asc">
                        <label for="asc">Croissant</label>
                        <input type="radio" name="sort" id="desc" value="desc" checked>
                        <label for="desc">Décroissant</label>
                    </div>

                    <div>

                    </div>
                </div>
            </div>
        </div>
        <!-- <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF;  ?>" /> -->

        <div class="btnSearchBloc">
            <button class="btnSearch" type="submit">Rechercher</button>
        </div>

    </form>
</section>