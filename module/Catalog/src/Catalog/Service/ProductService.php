<?php
 
 namespace Catalog\Service;

 use Catalog\Model\Product;
 use Catalog\Mapper\ProductMapperInterface;


 class ProductService implements ProductServiceInterface
 {
    protected $productMapper;

    public function __construct(ProductMapperInterface $productMapper)
    {
       $this->productMapper = $productMapper;
    }


     /**
      * {@inheritDoc}
      */
     public function findAllProducts()
     {
      return $this->productMapper->findAll();
     }

     /**
      * {@inheritDoc}
      */
     public function findProduct($id)
     {
         return $this->productMapper->find($id);

     }

     /**
      * {@inheritDoc}
      */
     public function saveOrderLinks( $data )
     {
         return $this->productMapper->saveOrderLinks( $data );

     }

     /**
      * {@inheritDoc}
      */
     public function getOrderLink( $id )
     {
         return $this->productMapper->getOrderLink( $id );

     }

     public function getOptions( )
     {
         return $this->productMapper->getOptions( );

     }

     public function getCategories( )
     {
         return $this->productMapper->getCategories( );

     }

     public function saveProducts( $data )
     {
         return $this->productMapper->saveProducts( $data );

     }

     public function getProducts( $where )
     {
         return $this->productMapper->getProducts( $where );

     }

     public function disableProducts( $parent_id, $ids )
     {
         return $this->productMapper->disableProducts( $parent_id, $ids );

     }
  
     public function getProductsByCategory( )
     {
         return $this->productMapper->getProductsByCategory( );

     } 

     public function getOrderdProducts( $pids = array(), $cat_ids = array() )
     {
         return $this->productMapper->getOrderdProducts( $pids , $cat_ids  );

     } 

     public function saveOrder( $data = array() )
     {
         return $this->productMapper->saveOrder( $data  );

     }   

     public function getOrder( $id = 0 )
     {
         return $this->productMapper->getOrder( $id  );

     }

     public function login($data )
     {
         return $this->productMapper->login( $data);

     } 
 }