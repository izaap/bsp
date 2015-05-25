<?php
 // Filename: /module/Blog/src/Blog/Mapper/ZendDbSqlMapper.php
 namespace Catalog\Mapper;

 use Catalog\Model\ProductInterface;
 use Zend\Db\Adapter\AdapterInterface;
 use Zend\Db\Adapter\Driver\ResultInterface;
 use Zend\Db\ResultSet\ResultSet;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\HydratorInterface;
 use Zend\Db\Sql\Sql;

 class ZendDbSqlMapper implements ProductMapperInterface
 {
     /**
      * @var \Zend\Db\Adapter\AdapterInterface
      */
     protected $dbAdapter;

     protected $hydrator;

     protected $productPrototype;

     /**
      * @param AdapterInterface  $dbAdapter
      */
     public function __construct(AdapterInterface $dbAdapter,HydratorInterface $hydrator,ProductInterface $productPrototype)
     {
         $this->dbAdapter = $dbAdapter;
         $this->hydrator       = $hydrator;
         $this->productPrototype  = $productPrototype;
     }

     /**
      * @param int|string $id
      *
      * @return PostInterface
      * @throws \InvalidArgumentException
      */
     public function find($id)
     {
           $sql    = new Sql($this->dbAdapter);
           $select = $sql->select('product');
           $select->where(array('id = ?' => $id));

           $stmt   = $sql->prepareStatementForSqlObject($select);
           $result = $stmt->execute();

           if ($result instanceof ResultInterface && $result->isQueryResult() && $result->getAffectedRows()) {
               return $this->hydrator->hydrate($result->current(), $this->productPrototype);
           }

           throw new \InvalidArgumentException("Product with given ID:{$id} not found.");
     }

     /**
      * @return array|PostInterface[]
      */
     public function findAll()
     {
         $sql    = new Sql($this->dbAdapter);
         $select = $sql->select('product');

         $stmt   = $sql->prepareStatementForSqlObject($select);
         $result = $stmt->execute();

         if ($result instanceof ResultInterface && $result->isQueryResult()) {
             $resultSet = new HydratingResultSet($this->hydrator, $this->productPrototype);

             return $resultSet->initialize($result);
         }

         return array();

     }
 }