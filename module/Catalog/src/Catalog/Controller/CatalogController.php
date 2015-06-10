<?php
	namespace Catalog\Controller;

	use Catalog\Service\ProductServiceInterface;
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use Zend\Form\Factory;
	use Zend\Validator;
	use Zend\Mvc\Router\RouteMatch;
	use Catalog\Form\UploadForm;

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
			$products_valid = '';
			if( count($_POST) )
			{
				$form->setData($_POST);
				if ($form->isValid()) 
				{
					if(isset($_POST['categories']) || isset($_POST['products']) || isset($_POST['essential']) || isset($_POST['clearance']))
					{
				    	$validatedData = $form->getData();

				    	if(isset($_POST['categories']))
				    		$validatedData['categories'] = $_POST['categories'];

				    	if(isset($_POST['products']))
				    		$validatedData['products'] = $_POST['products'];	

				    	if(isset($_POST['essential']))
				    		$validatedData['essential'] = $_POST['essential'];

				    	if(isset($_POST['clearance']))
				    		$validatedData['clearance'] = $_POST['clearance'];				

				    	//echo '<pre>';print_r($validatedData);die;
				    	$order_links_id = $this->productService->saveOrderLinks( array('order_data' => json_encode($validatedData)) );
				    }
				    else
				    {
				    	$products_valid = "Please Select atleast one product or category";
				    }
				} 
				else 
				{
			    	$messages = $form->getMessages();
			    }

			}
			
			$cat_products=array();
			$categories = array();
			$prods = $this->productService->getProductsByCategory();
			foreach ($prods as $row) 
			{
				$cat_products[$row['category_id']][]=$row;
				$categories[$row['category_id']] = $row['category_name'];
			}

			return new ViewModel(array(
             'data' => $this->productService->findAllProducts(),
             'form' => $form,
             'products' => $cat_products,
             'categories' => $categories,
             'product_validate' => $products_valid,
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
			
			$errors = array();
			$success_uploads = 0;

			$form = new UploadForm('upload-form');
			$request = $this->getRequest();
		    if ($request->isPost()) 
		    {
		        // Make certain to merge the files info!
		        $post = array_merge_recursive(
		            $request->getPost()->toArray(),
		            $request->getFiles()->toArray()
		        );

		        $form->setData($post);
		        if ($form->isValid()) 
		        {
		            $data = $form->getData();
		            
		            $result = $this->productService->getOptions( );
					$options = array();
					foreach ($result as $row) 
					{
						$tmp = strtolower($row['value']);
						$options[$row['attribute_id']][$tmp] = $row['id']; 
					}

					$result = $this->productService->getCategories( );
					$cats_id = array();
					$cats_name = array();
					foreach ($result as $row) 
					{
						$tmp = strtolower($row['name']);
						$cats_name[$row['id']] = $row['name']; 
						$cats_id[$tmp] = $row['id'];
					}

					$tmpName = $data['image-file']['tmp_name'];
		            if(($handle = fopen($tmpName, 'r')) !== FALSE) 
		            {
			            // necessary if a large csv file
			        	//echo "<pre>";
			        	$row=1;
				        while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) 
				        {

					        if($row==1)
					        {

				               $field_errors='';

				               foreach($data as $key => $col){

				               		if(!in_array(strtolower($col),$this->required_fields))
				               			$field_errors .= "The ".$col." field is missing<br/>";
				               }

				               if(!empty($field_errors)){
				               	   break;
				               }
				               
				           	}
				           	else
				           	{

					           	$data = array_combine($this->required_fields, array_values($data));
					            
					            $colors = explode(';', $data['color']);
					            $sizes 	= explode(';', $data['size']);

					            $category_name = strtolower(trim($data['category']));

				            	if( array_key_exists($category_name, $cats_id) === FALSE )
				            	{
				            		$errors[] = "<b>{$data['style']}:</b> The Category \"$category_name\" is missing in Category list.";
				            		continue;
				            	}

				            	$cat_id = $cats_id[$category_name];

				            	//get parent product if exists
				            	$pp = $this->productService->getProducts( array('sku=?' => $data['style']) )->current();
				            	$parent_id = 0;
				            	if( $pp === false || !count($pp) )
				            	{
				            		$p_insert = array();
				            		$p_insert['type'] = 'configurable';
					            	$p_insert['parent_id'] = 0;
					            	$p_insert['combination'] = "";
					            	$p_insert['sku'] = $data['style'];
					            	$p_insert['category_id'] = $cat_id;
					            	$p_insert['description'] = $data['description'];
					            	$p_insert['min_qty'] = $data['minimum'];
					            	$p_insert['price'] = (float)$data['price'];
					            	$p_insert['qty'] = 1000;
					            	$p_insert['clearance'] = $data['clearance'];
					            	$p_insert['essential'] = $data['essential'];

					            	$parent_id = $this->productService->saveProducts( $p_insert );

				            	}
				            	else
				            	{
				            		$parent_id = $pp['id'];
				            	}

				            	$child_ids = array();
					            foreach ($colors as $color) 
					            {
					            	$color = strtolower(trim($color));
					            	if( array_key_exists($color, $options[1]) === FALSE )
					            	{
					            		$errors[] = "<b>{$data['style']}:</b> The Color \"$color\" is missing in attribute list.";
					            		continue;
					            	}

					            	

					            	foreach ($sizes as $size) 
						            {
						            	$size = strtolower(trim($size));
						            	
						            	if( array_key_exists($size, $options[2]) === FALSE )
						            	{
						            		$errors[] = "<b>{$data['style']}:</b> The Size \"$size\" is missing in attribute list.";
						            		continue;
						            	}

						            	$color_id = $options[1][$color];
						            	$size_id = $options[2][$size];						            	

						            	$insert  = array();
						            	$insert['type'] = 'simple';
						            	$insert['parent_id'] = $parent_id;
						            	$insert['combination'] = "$color_id,$size_id";
						            	$insert['sku'] = "{$data['style']}-$color-$size";
						            	$insert['category_id'] = $cat_id;
						            	$insert['description'] = $data['description'];
						            	$insert['min_qty'] = $data['minimum'];
						            	$insert['price'] = (float)$data['price'];
						            	$insert['qty'] = 1000;
						            	$insert['clearance'] = $data['clearance'];
						            	$insert['essential'] = $data['essential'];

						            	$pid = $this->productService->saveProducts( $insert );

						            	$child_ids[] = $pid;

						            	$success_uploads++;
						            }
					            }


					            //update missing child's qty as 0 ( oos )
					            $this->productService->disableProducts( $parent_id, $child_ids );
				        	}

				            $row++;
			            
			            }

			            //echo '<pre>';
					    //print_r($errors);die;

			            fclose($handle);
			        }
		            
		        }
		    }

		    return array('form' => $form, 'errors' => $errors, 'success_uploads' => $success_uploads);

			
		}
	}