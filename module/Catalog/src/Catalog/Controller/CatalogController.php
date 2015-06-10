<?php
	namespace Catalog\Controller;

	use Catalog\Service\ProductServiceInterface;
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use Zend\Form\Factory;
	use Zend\Validator;
	use Zend\Mvc\Router\RouteMatch;

	class CatalogController extends AbstractActionController
	{
		protected $productService;

		protected $required_fields = array('style','description','category','color','size','price','minimum','clearance','essential');

		public function __construct(ProductServiceInterface $productService)
		{
			$this->productService = $productService;

		}

		public function indexAction()
		{
			$factory = new Factory();

			$form    = $factory->createForm(array(
					    'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable',
					    'elements' => array(
					        array(
					            'spec' => array( 'name' => 'name', 'attributes' => array('placeholder' => 'COMPANY NAME:'), 'type'  => 'Text' )
					        ),
					        array(
					            'spec' => array( 'name' => 'ship_to', 'attributes' => array('placeholder' => 'SHIP TO ADDRESS:'), 'type'  => 'Textarea' )
					        ),
					        array(
					            'spec' => array( 'name' => 'bill_to', 'attributes' => array('placeholder' => 'BILL TO ADDRESS:'), 'type'  => 'Textarea' )
					        ),
					        array(
					            'spec' => array( 'name' => 'notes', 'attributes' => array('placeholder' => 'BUYER’S NOTES:'), 'type'  => 'Textarea' )
					        ),
					        array(
					            'spec' => array( 'name' => 'start_date', 
					            				'attributes' => array('placeholder' => 'START DATE:', 'class' => 'startdate pull-left mr5', 'id' => 'start_date'),
					            				'type'  => 'Text' )
					        ),
					        array(
					            'spec' => array( 'name' => 'end_date',
					            				'attributes' => array('placeholder' => 'COMPLETION DATE:', 'class' => 'enddate pull-right ml5', 'id' => 'end_date'), 
					            				'type'  => 'Text' )
					        ),
					        array(
					            'spec' => array( 'name' => 'po', 'attributes' => array('placeholder' => 'PO#:'), 'type'  => 'Text' )
					        ),
					        array(
					            'spec' => array( 'name' => 'terms', 'attributes' => array('placeholder' => 'TERMS:'), 'type'  => 'Text' )
					        ),
					        array(
					            'spec' => array( 'name' => 'buyer_email', 'attributes' => array('placeholder' => 'BUYERS EMAIL:'), 'type'  => 'Text' )
					        ),
					        array(
					            'spec' => array( 'name' => 'executive', 'attributes' => array('placeholder' => 'ACCOUNT EXECUTIVE:'), 'type'  => 'Text' )
					            )

					    ),

					    'input_filter' => array(
					    		'name' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty'),
					    				)
					    			),
					    		'ship_to' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'bill_to' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'notes' => array(
					    			'required' => false,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'start_date' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'end_date' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'po' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'terms' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'buyer_email' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			),
					    		'executive' => array(
					    			'required' => true,
					    			'validators' => array(
					    				array('name' => 'NotEmpty')
					    				)
					    			)
					    	)
					 )
					);

			

			$order_links_id = '';
			if( count($_POST) )
			{
				$form->setData($_POST);
				if ($form->isValid()) 
				{
			    	$validatedData = $form->getData();
			    	//echo '<pre>';print_r($validatedData);die;
			    	$order_links_id = $this->productService->saveOrderLinks( array('order_data' => json_encode($validatedData)) );

				} 
				else 
				{
			    	$messages = $form->getMessages();
			    }

			}
			
			return new ViewModel(array(
             'data' => $this->productService->findAllProducts(),
             'form' => $form,
             'order_links_id' => $order_links_id
         	));

		}

		public function checklistAction()
		{
				
			if( count($_POST) )
			{
				return $this->redirect()->toRoute('order', array('id' => '1234'));
			}

			return array();
		}

		public function orderAction( )
		{
			$id = (int) $this->params()->fromRoute('id', 0);
			

			$r = $this->productService->getOrderLink($id);
			$row = $r->current();

			return new ViewModel(array(
             'id' => $id,
             'order_data' => json_decode($row['order_data'])
         	));

			return array();
		}

		public function deleteAction()
		{
		}

		public function uploadAction()
		{
			$factory = new Factory();

			$form    = $factory->createForm(array(
					    'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable',
					    'elements' => array(
					        array(
					            'spec' => array( 'name' => 'file', 'attributes' => array('placeholder' => 'UPLOAD FILE:'), 'type'  => 'file' )
					        )
					        
					    ),

					    'input_filter' => array(
					    		'file' => array(
					    			'required' => false,
					    			'validators' => array(
					    				array('name' => 'NotEmpty'),
					    				)
					    			)

					    	)
					 )
					);

			if(isset($_FILES["file"]))
			{

				if($_FILES['file']['error'] == 0){

				    $name = $_FILES['file']['name'];
				    echo $name;
				    $ext = explode(".", $_FILES['file']['name']);
				    $ext = strtolower(end($ext));
				    $type = $_FILES['file']['type'];
				    $tmpName = $_FILES['file']['tmp_name'];

				    
				    // check the file is a csv
				    if($ext === 'csv'){
				        if(($handle = fopen($tmpName, 'r')) !== FALSE) {
				            // necessary if a large csv file
				        	echo "<pre>";
				        	$row=1;
					        while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {

					        if($row==1){

				               $field_errors='';

				               foreach($data as $key => $col){

				               		if(!in_array(strtolower($col),$this->required_fields))
				               			$field_errors .= "The ".$col." field is missing<br/>";
				               }

				               if(!empty($field_errors)){
				               	   break;
				               }
				               
				           	}else{

					           	$data = array_combine($this->required_fields, array_values($data));
					            print_r($data);
				        	}

				            $row++;
				            
				            }
				            fclose($handle);
				        }
				    }
				}
				exit;
			}	



			return new ViewModel(array(
             'data' => $this->productService->findAllProducts(),
             'form' => $form
         	));
		}
	}