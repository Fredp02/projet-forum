<?php
//dd($categories);
?>

<section class="dbCategoriesList m-auto">
    <div class="text-center">
        <h1>Dashboard</h1>
        <h2>Liste des catégories</h2>
    </div>
    <table class="table table-dark table-striped text-center align-middle">
        <thead>
        <tr>
            <th scope="col">Nom de la catégories</th>
            <th scope="col">Nom du parent</th>
            <th scope="col" colspan="2">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $category) : ?>
            <tr>
                <td><?= $category->categoryName; ?></td>
                <td><?= $category->parentCategoryName ?? ''; ?></td>
                <td><a href="?controller=dashboard&action=categoryEdit&id=<?= $category->categoryID; ?>"
                       class="btn btn-warning"><i class="fa-solid fa-pen" aria-hidden="true"></i></a></td>
                <td><a onclick="return confirm('Certain de vouloir supprimer cette catégorie ?')" href="?controller=dashboard&action=categoryDelete&id=<?= $category->categoryID; ?>"
                       class="btn btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="text-center">
        <a class="btn btn-warning me-3" href="?controller=dashboard">Retour au Dashboad</a>

        <a class="btn btn-primary m-3" href="?controller=dashboard&action=categoryAdd">
            Ajouter une catégorie
        </a>
    </div>

</section>
