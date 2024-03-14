<?php
//dd($parentCategories);
?>

<section class="dbCategoriesList m-auto w-50">
    <div class="text-center">
        <h1>Dashboard</h1>
        <h2><?= $pageTitle; ?></h2>
    </div>
    <div>
        <div class="alertForm"></div>
        <form class="row g-3 needs-validation">
            <div class="input-group">
                <label for="name " class="input-group-text">Nom</label>
                <input type="text" name="name" class="form-control name" value="<?= $categoryName ?? '' ?>">
                <div class="invalid-feedback">
                    Le nom de la catégorie est requis
                </div>
            </div>
            <div class="input-group">
                <label for="description " class="input-group-text">Description</label>
                <textarea class="form-control description" name="description" id="description" style="height: 100px"><?= $categoryDescription ?? '' ?></textarea>
                <div class="invalid-feedback">
                    Une description de la catégorie est requise
                </div>
            </div>
            <div class="form-check ms-2">
                <?php if(isset($categoryParentID)):?>
                    <input class="form-check-input" name="isParent" type="checkbox" id="flexCheckDefault"
                           <?php if ($categoryParentID === null): ?>
                           checked
                           <?php endif; ?>
                    >
                <?php else :?>
                    <input class="form-check-input" name="isParent" type="checkbox" id="flexCheckDefault" checked>
                <?php endif; ?>


                <label class="form-check-label" for="flexCheckDefault">
                    Définir comme catégorie parente
                </label>
            </div>
            <div class="input-group ">
                <!-- is-invalid sur le select -->
<!--                $categoryParentID === $parentCategory->categoryID-->
                <select name="parentCategorie" class="form-select"
                    <?php if(isset($edit) ):?>
                        <?php if ($categoryParentID === null):?>
                        disabled style="opacity: 0.3"
                        <?php endif; ?>
                    <?php else : ?>
                        disabled style="opacity: 0.3"
                    <?php endif; ?>

                >
                    <option value="">Selection de la catégorie parente</option>
                    <?php foreach ($parentCategories as $parentCategory) : ?>
                        <option value="<?= $parentCategory->categoryID ?>"
                        <?php if (isset($categoryParentID) && $categoryParentID === $parentCategory->categoryID):?>
                            selected
                        <?php endif; ?>
                        >
                        <?= $parentCategory->categoryName ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback mb-4">
                    Si la case du dessus n'est pas cochée, il faut définir une catégorie parente.
                </div>
            </div>

            <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF; ?>">
            <div class="text-center mb-5">
                <a class="btn btn-warning me-3" href="?controller=dashboard&action=categoriesListShow">Retour à la liste</a>

                <button type="submit" class="btn btn-primary btnForm "><?= $action;?></button>
            </div>
        </form>
    </div>


</section>
