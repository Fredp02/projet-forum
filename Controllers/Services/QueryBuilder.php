<?php

namespace Controllers\Services;

class QueryBuilder
{
    private string $query;

    public function __construct(
        private array $queryData,
        private bool  $paginate,
        private ?int  $limite,
        private ?int  $offset
    )
    {
    }

    public function create(): string
    {
        // Construire la requête SQL en fonction des critères de recherche spécifiés
        $this->query = "
        SELECT topics.*, messages.*, COUNT(messages.messageID) AS totalMessages, users.userID, users.pseudo, categorys.categoryName
        FROM topics
        JOIN messages ON messages.topicID = topics.topicID
        JOIN users ON messages.userID = users.userID
        JOIN categorys ON topics.categoryID = categorys.categoryID
        ";
        if ($this->queryData['title']) {
            // Recherche dans le titre seulement
            $this->query .= "WHERE topics.topicTitle LIKE :string";
        } else {
            // Recherche dans tous les champs pertinents
            $this->query .= "WHERE (topics.topicTitle LIKE :string OR messages.messageText LIKE :string)";
        }
        if (!empty($this->queryData['author'])) {
            // Filtre par auteur
            $this->query .= " AND users.pseudo ='$this->queryData['author']'";
        }
        //si date début mais pas de date fin
        if (!empty($this->queryData['from']) && empty($this->queryData['to'])) {
            // Filtre par date
            $this->query .= " AND messageDate >= '$this->queryData['from']'";
        }
        //si date fin mais pas date début
        if (empty($this->queryData['from']) && !empty($this->queryData['to'])) {
            // Filtre par date
            $this->query .= " AND messageDate <= '$this->queryData['to']'";
        }
        //si date début et date fin
        if (!empty($this->queryData['from']) && !empty($this->queryData['to'])) {
            // Filtre par date
            $this->query .= " AND messageDate BETWEEN '$this->queryData['from']' AND '$this->queryData['to']'";
        }

        //Si le select contient des données ET si il n'y a pas la valeur 'all'
        if (!empty($this->queryData['select']) && !in_array('all', $this->queryData['select'])) {

            //Cette ligne utilise la fonction array_filter pour filtrer les éléments du tableau $array qui se terminent par la chaîne '-p'. Ces éléments représentent les ID des catégories parentes sélectionnées par l’utilisateur. La fonction substr est utilisée pour vérifier si chaque élément du tableau se termine par '-p'. Si c’est le cas, l’élément est conservé dans le tableau filtré $parentCategories.
            //on ne modifie pas encore ce tableau car on va avoir besoin de faire une comparaison dans la ligne suivante
            $parentCategories = array_filter($this->queryData['select'], fn($value) => substr($value, -2) === '-p');

            //Cette ligne utilise la fonction array_diff pour voir la différence entre les tableaux $array et $parentCategories. Le résultat est un nouveau tableau $childCategories contenant les éléments de $array qui ne sont pas présents dans $parentCategories. Ces éléments représentent les ID des catégories enfants sélectionnées par l’utilisateur.
            $childCategories = array_diff($this->queryData['select'], $parentCategories);

            //Cette ligne utilise la fonction array_map pour supprimer la chaîne '-p' de chaque élément du tableau $parentCategories. La fonction substr est utilisée pour renvoyer une sous-chaîne de chaque élément en supprimant les deux derniers caractères ('-p'). Le résultat est un nouveau tableau $parentCategories contenant les ID des catégories parentes sélectionnées par l’utilisateur, sans la chaîne '-p'.
            $parentCategories = array_map(fn($value) => substr($value, 0, -2), $parentCategories);

            //si l’un des tableaux $childCategories ou $parentCategories n’est pas vide. Si l’un de ces tableaux n’est pas vide, cela signifie que des conditions supplémentaires doivent être ajoutées à la chaîne SQL pour spécifier les catégories pour lesquelles effectuer la recherche. La chaîne " AND (" est ajoutée à la variable $sql pour commencer une nouvelle condition dans la clause WHERE.
            if (!empty($childCategories) || !empty($parentCategories)) {
                $this->query .= " AND (";

                //Ces lignes vérifient si le tableau $childCategories n’est pas vide. Si ce tableau n’est pas vide, cela signifie que des catégories enfants ont été sélectionnées par l’utilisateur et doivent être incluses dans la recherche. La fonction implode est utilisée pour concaténer les éléments du tableau $childCategories en une chaîne, en utilisant une virgule comme séparateur. Cette chaîne est ensuite utilisée pour construire une condition IN dans la clause WHERE, qui spécifie que la colonne categoryID doit être égale à l’un des ID de catégories enfants spécifiés dans le tableau $childCategories. Cette condition est ajoutée à la variable $sql.
                if (!empty($childCategories)) {
                    $childCategoriesString = implode(',', $childCategories);
                    $this->query .= "topics.categoryID IN ($childCategoriesString)";
                }

                //Ces lignes vérifient si le tableau $parentCategories n’est pas vide. Si ce tableau n’est pas vide, cela signifie que des catégories parentes ont été sélectionnées par l’utilisateur et doivent être incluses dans la recherche. Si le tableau $childCategories n’est pas vide, cela signifie que la condition précédente a été ajoutée à la variable $sql, donc un opérateur OR est ajouté pour combiner les deux conditions. La fonction implode est utilisée pour concaténer les éléments du tableau $parentCategories en une chaîne, en utilisant une virgule comme séparateur. Cette chaîne est ensuite utilisée pour construire une sous-requête dans la clause WHERE, qui récupère les ID des catégories enfants des catégories parentes spécifiées dans le tableau $parentCategories. Cette sous-requête est utilisée pour construire une condition IN, qui spécifie que la colonne categoryID doit être égale à l’un des ID de catégories enfants renvoyés par la sous-requête. Cette condition est ajoutée à la variable $sql.
                if (!empty($parentCategories)) {
                    if (!empty($childCategories)) {
                        $this->query .= " OR ";
                    }
                    $parentCategoriesString = implode(',', $parentCategories);
                    $this->query .= "topics.categoryID IN (SELECT categoryID FROM categorys WHERE categoryParentID IN ($parentCategoriesString))";
                }

                //cette ligne ferme la parenthèse ouverte précédemment pour terminer la condition ajoutée à la clause WHERE.
                $this->query .= ")";
            }
        }
        $this->query .= " GROUP BY topics.topicID, messages.messageID";

        // Tri des résultats
        if (!empty($this->queryData['order'])) {
            switch ($this->queryData['order']) {
                case 'forum':
                    $this->query .= " ORDER BY categorys.categoryName";
                    break;

                case 'title':
                    $this->query .= " ORDER BY topics.topicTitle";
                    break;

                case 'author':
                    $this->query .= " ORDER BY users.pseudo";
                    break;

                default:
                    $this->query .= " ORDER BY messages.messageDate";
                    break;
            }
        }
        if (!empty($this->queryData['sort'])) {
            switch ($this->queryData['sort']) {
                case 'asc':
                    $this->query .= " ASC";
                    break;

                case 'desc':
                    $this->query .= " DESC";
                    break;
            }
        }
        if ($this->paginate) {
            $this->query .= " LIMIT $this->limite OFFSET $this->offset";
        }

        return $this->query;
    }
}
