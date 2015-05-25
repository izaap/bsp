<?php
 namespace Catalog\Mapper;

 use Catalog\Model\ProductInterface;

 interface ProductMapperInterface
 {
     
     public function find($id);

     
     public function findAll();
 }