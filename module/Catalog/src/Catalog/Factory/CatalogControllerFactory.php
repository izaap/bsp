<?php
 
 namespace Catalog\Factory;

 use Catalog\Controller\CatalogController;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;

 class CatalogControllerFactory implements FactoryInterface
 {
     public function createService(ServiceLocatorInterface $serviceLocator)
     {
         $realServiceLocator = $serviceLocator->getServiceLocator();
         $productService        = $realServiceLocator->get('Catalog\Service\ProductServiceInterface');

         return new CatalogController($productService);
     }
 }