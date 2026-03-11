<?php

namespace app\models;   

class Produit
{
    public $produits;

    public function __construct()
    {
        $this->produits = [
            ['id' => 1, 'produit' => 'Rose'],
            ['id' => 2, 'produit' => 'Tulipe'],
            ['id' => 3, 'produit' => 'Jasmin'],
            ['id' => 4, 'produit' => 'Laurier Rose'],
            ['id' => 5, 'produit' => 'Orchidée'],
        ];
    }
}
