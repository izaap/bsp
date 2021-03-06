<?php
    return array(
     'controllers' => array(
         'factories' => array(
             'Catalog\Controller\Catalog' => 'Catalog\Factory\CatalogControllerFactory',
         ),
     ),

    'db' => array(
         'driver'         => 'Pdo',
         'username'       => 'root',  //edit this
         'password'       => '',  //edit this
         'dsn'            => 'mysql:dbname=bsp;host=localhost',
         'driver_options' => array(
             \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
         )
     ),
     

    'service_manager' => array(
         'factories' => array(
                            'Catalog\Mapper\ProductMapperInterface'   => 'Catalog\Factory\ZendDbSqlMapperFactory',
                            'Catalog\Service\ProductServiceInterface' => 'Catalog\Factory\ProductServiceFactory',
                            'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
         )
     ),


     'router' => array(
         'routes' => array(
             'catalog' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/ram[/:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'Catalog\Controller\Catalog',
                         'action'     => 'index',
                     ),
                 ),
             ),

             'confirmation' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/order/confirmation[/:id]',
                     'constraints' => array( ),
                     'defaults' => array(
                         'controller' => 'Catalog\Controller\Catalog',
                         'action'     => 'confirmation',
                     ),
                 ),
             ),

             'order' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/order[/:id]',
                     'constraints' => array( 'id'     => '[0-9]+' ),
                     'defaults' => array(
                         'controller' => 'Catalog\Controller\Catalog',
                         'action'     => 'order',
                     ),
                 ),
             ),

             'upload' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/inventory_upload',
                     'constraints' => array(  ),
                     'defaults' => array(
                         'controller' => 'Catalog\Controller\Catalog',
                         'action'     => 'upload',
                     ),
                 ),
             ),

              'login' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/login',
                     'constraints' => array(  ),
                     'defaults' => array(
                         'controller' => 'Catalog\Controller\Catalog',
                         'action'     => 'login',
                     ),
                 ),
             ),

               'logout' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/logout',
                     'constraints' => array(  ),
                     'defaults' => array(
                         'controller' => 'Catalog\Controller\Catalog',
                         'action'     => 'logout',
                     ),
                 ),
             ),

         ),
     ),

     'view_manager' => array(
         'template_path_stack' => array(
             'catalog' => __DIR__ . '/../view',
         ),
     ),
    );