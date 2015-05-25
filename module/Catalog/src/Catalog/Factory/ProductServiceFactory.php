<?php
 namespace Catalog\Factory;

 use Catalog\Service\ProductService;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;

 class ProductServiceFactory implements FactoryInterface
 {
     /**
      * Create service
      *
      * @param ServiceLocatorInterface $serviceLocator
      * @return mixed
      */
     public function createService(ServiceLocatorInterface $serviceLocator)
     {
         return new ProductService(
             $serviceLocator->get('Catalog\Mapper\ProductMapperInterface')
         );
     }
 }