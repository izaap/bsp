<?php
 
 namespace Catalog\Service;

 use Catalog\Model\ProductInterface;

 interface ProductServiceInterface
 {
    public function findAllProducts();

    public function findProduct($id);
 }