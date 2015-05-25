<?php
 // Filename: /module/Blog/src/Blog/Factory/ZendDbSqlMapperFactory.php
 namespace Catalog\Factory;

 use Catalog\Mapper\ZendDbSqlMapper;
 use Catalog\Model\Product;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;
 use Zend\Stdlib\Hydrator\ClassMethods;

 class ZendDbSqlMapperFactory implements FactoryInterface
 {
     /**
      * Create service
      *
      * @param ServiceLocatorInterface $serviceLocator
      *
      * @return mixed
      */
     public function createService(ServiceLocatorInterface $serviceLocator)
     {
         return new ZendDbSqlMapper(
             $serviceLocator->get('Zend\Db\Adapter\Adapter'),
             new ClassMethods(false),
             new Product()
         );
     }
 }