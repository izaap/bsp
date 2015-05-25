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
 }