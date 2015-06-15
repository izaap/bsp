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
 use Zend\Db\Sql\Where;
 
 use Zend\Session\Container;
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
        $data['id'] = isset($data['id'])?$data['id']:0;
        $link = $this->getOrderLink( $data['id'] )->current();

        if( $link === FALSE || !count($link) )
        {
          $action = new Insert('order_links');
          $action->values($data);
        }
        else
        {
          $action = new Update('order_links');
          $action->set($data);
          $action->where(array('id = ?' => $data['id']));
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

          return $data['id'];
        }

        return false;
     }

     public function getOrderLink( $id )
     {
           if( !$id )
            return array();

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
     public function getProducts( $data )
     {
           $sql    = new Sql($this->dbAdapter);
           $select = $sql->select('product');

           if( isset($data['category_id']) and isset($data['id']) )
           {
              $select->join(array('parent' => 'product'), "product.parent_id = parent.id ", array('parent_sku'=>'sku'), $select::JOIN_LEFT);
              $where = new Where();
              $where->in('product.category_id', $data['category_id']);
              $where->OR->in('product.id', $data['id'] );

              $select->where( $where );
           }
           else
           {
              $select->where( $data );
           }           

           $qry = $sql->getSqlStringForSqlObject($select);
           echo $qry;die;

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

     public function getProductsByCategory()
     {
         $sql    = new Sql($this->dbAdapter);
         $select = $sql->select();

         $select->from('product','*');
         $select->join('category', "product.category_id = category.id ", array('category_name'=>'name'));
         $select->where( array('product.qty > ?' => 0) );

         $stmt   = $sql->prepareStatementForSqlObject($select);
         $result = $stmt->execute();

         if ($result instanceof ResultInterface && $result->isQueryResult()) {
             return $result;
         }

         return array();

     } 

     public function getOrderdProducts( $pids = array(), $cat_ids = array() )
     {

        if( !count($pids) && !count($cat_ids) )
          return array();

        $app_str = "";

        if(count($cat_ids))
        {
          $cat_ids  = implode(',', $cat_ids);
          $app_str .= "category_id IN ($cat_ids) ";
        }

        if(count($pids))
        {
          $pids     = implode(',', $pids);
          $app_str .= $app_str != ''? " OR ":'';
          $app_str .= " id IN ($pids) ";
        }
        
        
        $sql = "SELECT p.*,t.sku as parent_sku FROM (
                                SELECT parent_id,sku from product 
                                  WHERE ( $app_str )  
                                    AND parent_id > 0 group by parent_id 
                              ) t 
                          JOIN product p ON(p.parent_id=t.parent_id OR p.id=t.parent_id) 
                          ORDER BY p.id DESC";
        //echo $sql;die;                  
        $statement = $this->dbAdapter->query($sql); 

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) 
        {
          return $result;
        }
        return array();
     } 

     public function saveOrder( $data = array() )
     {

        $action = new Insert('order');
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

          return false;
        }

        return false;
     }

     /**
      * @return array|PostInterface[]
      */
     public function getOrder( $id = 0 )
     {
         $sql    = new Sql($this->dbAdapter);
         $select = $sql->select();

         $select->from('order');
         $select->where(array('id = ?' => $id));

         $stmt   = $sql->prepareStatementForSqlObject($select);
         $result = $stmt->execute();

         if ($result instanceof ResultInterface && $result->isQueryResult()) 
         {
             return $result;
         }

         return array();

     }

 }