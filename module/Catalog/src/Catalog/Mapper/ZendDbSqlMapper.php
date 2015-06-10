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
 use Zend\Db\Sql\Insert;
 use Zend\Db\Sql\Update;

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

     public function saveOrderLinks( $data = array() )
     {
        $action = new Insert('order_links');
        $action->values($data);

        $sql    = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);

        $result = $stmt->execute();

        if ($result instanceof ResultInterface) 
        {
          if ($newId = $result->getGeneratedValue()) 
          {
            return $newId;
          }
        }

        return false;
     }

     public function getOrderLink( $id )
     {
           $sql    = new Sql($this->dbAdapter);
           $select = $sql->select('order_links');
           $select->where(array('id = ?' => $id));

           $stmt   = $sql->prepareStatementForSqlObject($select);
           $result = $stmt->execute();

           if ($result instanceof ResultInterface && $result->isQueryResult() && $result->getAffectedRows()) 
           {
               return $result;
           }

           throw new \InvalidArgumentException("Product with given ID:{$id} not found.");
     }


     /**
      * @return array|PostInterface[]
      */
     public function getOptions()
     {
         $sql    = new Sql($this->dbAdapter);
         $select = $sql->select();

         $select->from('attribute_options');
         $select->join('attribute', 'attribute_options.attribute_id = attribute.id', array('name'));

         $stmt   = $sql->prepareStatementForSqlObject($select);
         $result = $stmt->execute();

         if ($result instanceof ResultInterface && $result->isQueryResult()) 
         {
             return $result;
         }

         return array();

     }

     /**
      * @return array|PostInterface[]
      */
     public function getCategories()
     {
         $sql    = new Sql($this->dbAdapter);
         $select = $sql->select();

         $select->from('category');

         $stmt   = $sql->prepareStatementForSqlObject($select);
         $result = $stmt->execute();

         if ($result instanceof ResultInterface && $result->isQueryResult()) 
         {
             return $result;
         }

         return array();

     }

     public function saveProducts( $data = array() )
     {

        $product = $this->getProducts( array('sku = ?'=> $data['sku']) )->current();

        if( $product === FALSE || !count($product) )
        {
          $action = new Insert('product');
          $action->values($data);
        }
        else
        {
          $action = new Update('product');
          $action->set($data);
          $action->where(array('id = ?' => $product['id']));
        }
        

        $sql    = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);

        $result = $stmt->execute();

        if ($result instanceof ResultInterface) 
        {
          if ($newId = $result->getGeneratedValue()) 
          {
            return $newId;
          }

          return $product['id'];
        }

        return false;
     }


     /**
      * @param int|string $id
      *
      * @return PostInterface
      * @throws \InvalidArgumentException
      */
     public function getProducts( $where )
     {
           $sql    = new Sql($this->dbAdapter);
           $select = $sql->select('product');
           $select->where( $where );

           $stmt   = $sql->prepareStatementForSqlObject($select);
           $result = $stmt->execute();

           if ($result instanceof ResultInterface && $result->isQueryResult() ) 
           {
               return $result;
           }

           return FALSE;
     }


     /**
      * @param int|string $id
      *
      * @return PostInterface
      * @throws \InvalidArgumentException
      */
     public function getParentId( $where )
     {
           $sql    = new Sql($this->dbAdapter);
           $select = $sql->select('product');
           $select->where( $where );

           $stmt   = $sql->prepareStatementForSqlObject($select);
           $result = $stmt->execute();

           if ($result instanceof ResultInterface && $result->isQueryResult() ) 
           {
               return $result;
           }

           return FALSE;
     }

     public function disableProducts( $parent_id = 0, $ids = array() )
     {

        if( !count($ids) || !$parent_id )
          return false;

        $ids = implode(',', $ids);
        $sql = "UPDATE  product set qty='0' where parent_id='$parent_id' and id NOT IN ($ids)";
        $statement = $this->dbAdapter->query($sql); 

        return $statement->execute();
     }

 }