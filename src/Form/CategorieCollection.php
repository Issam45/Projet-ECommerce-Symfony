<?php

namespace App\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CategorieCollection {

    private $categories;

    public function __construct($categories)
    {
        $this->categories = new ArrayCollection($categories);
    }

    public function getCategories() : Collection 
    {
        return $this->categories;
    }

}