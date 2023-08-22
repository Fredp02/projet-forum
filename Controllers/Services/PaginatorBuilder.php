<?php

namespace Controllers\Services;

use Exception;
use Models\SearchModel;

class PaginatorBuilder
{
    private const LIMITE = 2;
    private int $nbrLinksPaginator = 3;
    private SearchModel $searchModel;
    public function __construct(
        private array $queryData,
        private  int $numPage,
        private  int $nombreResultatTotal,
        private  string $string,
    )
    {
        $this->searchModel = new SearchModel();
    }

    public function getLimite(): int
    {
        return self::LIMITE;
    }
    /**
     * @throws Exception
     */
    public function create(): array
    {
        //Je détermine le nombre de résultats visible dans la page
        //ainsi que le nombre total de pages pour gérer l'affichage du chevron droit :
        $nombrePageTotal = (int)ceil($this->nombreResultatTotal / self::LIMITE);
        // Attention ici, je convertis $nombrePageTotal en "int" car le "ceil" garde la variable en "float". Cela permettra ensuite d'utiliser la stricte égalité.
        if ($this->numPage > $nombrePageTotal) {
            return throw new Exception('Aucun résultat');
        }
        //on calcule l'offset en fonction du numéro de la page et de la limite
        $offset = ($this->numPage - 1) * self::LIMITE;
        //et on initialise la requete finale avec la pagination = "true" + limite et offset
        $queryPaginated = new QueryBuilder($this->queryData, true, self::LIMITE, $offset);

        //Et on lance le model pour récupérer les résultats
        $result = $this->searchModel->search($queryPaginated->create(), $this->string);


        //On initialise l'uri sans la valeur du numéro de la page. Cela nous permettra dans la vue,
        // de concaténer l'uri avec un numéro de page spécifique à la pagination.
        $baseUri = preg_replace('/&numPage=\d+/', '', $_SERVER['REQUEST_URI']) . '&numPage=';

        $targetPage = -1;
        if ($this->numPage === 1) {//Si on est sur la page 1, $targetPage = 0
            $targetPage = 0;
        }
        if ($nombrePageTotal < $this->nbrLinksPaginator) {
            $this->nbrLinksPaginator = $nombrePageTotal;
        } elseif ($this->numPage === $nombrePageTotal) { //si on est sur la dernière page
            $targetPage = -2;
        }

        return [
            'result' => $result,
            'baseUri' => $baseUri,
            'targetPage' => $targetPage,
            'nombrePageTotal' => $nombrePageTotal,
            'nbrLinksPaginator' => $this->nbrLinksPaginator
        ];


    }
}