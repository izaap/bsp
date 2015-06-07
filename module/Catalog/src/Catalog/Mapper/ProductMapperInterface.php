<?php
 namespace Catalog\Mapper;

 use Catalog\Model\ProductInterface;

 interface ProductMapperInterface
 {
     
     public function find($id);

     
     public function findAll();

     public function saveOrderLinks( $data );

     public function getOrderLink( $id );

 }