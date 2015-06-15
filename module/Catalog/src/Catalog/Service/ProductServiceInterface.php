<?php
 
 namespace Catalog\Service;

 use Catalog\Model\ProductInterface;

 interface ProductServiceInterface
 {
    public function findAllProducts();

    public function findProduct($id);

    public function saveOrderLinks( $data );

    public function getOrderLink( $id );

    public function getOptions( );
    
    public function getCategories( );

    public function saveProducts( $data );

    public function getProducts( $where );

    public function disableProducts( $parent_id, $ids );

    public function getProductsByCategory( );

    public function getOrderdProducts( $pids = array(), $cat_ids = array() );

    public function saveOrder( $data );

    public function getOrder( $data );

 }