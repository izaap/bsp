<?php
	namespace Catalog\Controller;

	use Catalog\Service\ProductServiceInterface;
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use Zend\Form\Factory;
	use Zend\Validator;
	use Zend\Mvc\Router\RouteMatch;
	use Catalog\Form\UploadForm;

	use Zend\Session\Container;

	class CatalogController extends AbstractActionController
	{
		protected $productService;

		protected $required_fields = array('style','description','category','color','size','price','minimum','clearance','essential');
		protected $server_url;

		public function __construct(ProductServiceInterface $productService)
		{
			$this->productService = $productService;
			

		}	

		public function indexAction()
		{
			$this->server_url = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost();
			
			$form = $this->getForm();

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
				    	{
				    		$validatedData['categories'] = $_POST['categories'];
				    	}
				    	else
				    	{
				    		$validatedData['categories'] = array();
				    	}

				    	if(isset($_POST['products']))
				    	{
				    		$validatedData['products'] = $_POST['products'];	
				    	}
				    	else
				    	{
				    		$validatedData['products'] = array();	
				    	}

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
			
			$result = $this->productService->getOptions( );
			$options = array();
			foreach ($result as $row) 
			{
				$tmp = strtolower($row['value']);
				$options[$row['attribute_id']][$tmp] = $row['id']; 
			}

			$colors = array_values($options[1]);

			//echo '<pre>';print_r($options[1]);

			$cat_products=array();
			$categories = array();
			$prods = $this->productService->getProductsByCategory();
			$pc = array();
			foreach ($prods as $row) 
			{
				if( !$row['parent_id'] )
					continue;

				$combinations = explode(',', $row['combination']);

				if( isset($pc[$row['parent_id']]) && in_array($combinations[0], $pc[$row['parent_id']]))
					continue;

				$pc[$row['parent_id']][] = $combinations[0];


				$tmp = explode('-', $row['sku']);
				array_pop( $tmp ); 
				
				$row['sku'] = implode('-', $tmp);

				$cat_products[$row['category_id']][]=$row;
				$categories[$row['category_id']] = $row['category_name'];
			}

			return new ViewModel(array(
             'data' => $this->productService->findAllProducts(),
             'form' => $form,
             'products' => $cat_products,
             'categories' => $categories,
             'product_validate' => $products_valid,
             'order_links_id' => $order_links_id,
             'server_url' => $this->server_url
         	));

		}

		public function confirmationAction()
		{

			$this->server_url = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost();

			//get params 
			$order_id = (int) $this->params()->fromRoute('id', 0);

			$order_details = $this->productService->getOrder($order_id)->current();

			$cart_data = json_decode($order_details['cart_data'], TRUE);
			//echo '<pre>';print_r($cart_data);
			$order_link_id = $order_details['order_link_id'];

			//get orderlinks data
			$orderlinks_data = $this->productService->getOrderLink($order_link_id)->current();
			$orderlinks_data = json_decode($orderlinks_data['order_data'], TRUE);


			//get options aaray
			$result = $this->productService->getOptions( );
			$options = array();
			foreach ($result as $row) 
			{
				$options[$row['attribute_id']][$row['id']] = $row['value']; 
			}
			

			$category = is_array($orderlinks_data['categories'])?$orderlinks_data['categories']:array();
			$products = is_array($orderlinks_data['products'])?$orderlinks_data['products']:array();

			$temp = array();
			foreach ($products as $cat_id => $pids) 
			{
				if( in_array($cat_id, $category) === FALSE)
				{
					$temp = array_merge($temp, $pids);
				}
			}
			$products = $temp;

			$result = $this->productService->getOrderdProducts( $products, $category );

			$pdetails 		= array();
			$parent_details = array();
			
			$ordered_parent_products = array_keys( $cart_data['products'] );
			
			$order_total = 0;
			foreach ($result as $row) 
			{
				
				
				$parent_id = $row['parent_id'];
				
				if( !$parent_id )
				{
					if( in_array($row['id'], $ordered_parent_products) )
					{
						$row['order_qty'] = array_sum( $cart_data['products'][$row['id']] );
						$row['sub_total'] = $row['order_qty']*$row['price'];
						$order_total += $row['sub_total'];
						$parent_details[$row['id']] = $row;
					}
					
					continue;
				}


				$combinations = explode(',', $row['combination']);
				
				$color_id 	= current($combinations);
				$size_id 	= next($combinations);
				
				$row['size_id'] = $size_id;

				$pdetails[$parent_id][$color_id][$size_id] = $row;
				
			}


			return new ViewModel(array(
			 'server_url' => $this->server_url,	
             'order_id' => $order_id,
             'order_data' => (object)$orderlinks_data,
             'parent_details' => $parent_details,
             'pdetails' => $pdetails,
             'options' => $options,
             'cart_data' => $cart_data,
             'order_total' => $order_total
         	));
		}

		public function orderAction( )
		{
			
			$this->server_url = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost();

			//get params 
			$id = (int) $this->params()->fromRoute('id', 0);
			//get orderlinks data
			$orderlinks_data = $this->productService->getOrderLink($id)->current();
			$orderlinks_data = json_decode($orderlinks_data['order_data'], TRUE);


			//get options aaray
			$result = $this->productService->getOptions( );
			$options = array();
			foreach ($result as $row) 
			{
				$options[$row['attribute_id']][$row['id']] = $row['value']; 
			}
			

			$category = is_array($orderlinks_data['categories'])?$orderlinks_data['categories']:array();
			$products = is_array($orderlinks_data['products'])?$orderlinks_data['products']:array();

			$temp = array();
			foreach ($products as $cat_id => $pids) 
			{
				if( in_array($cat_id, $category) === FALSE)
				{
					$temp = array_merge($temp, $pids);
				}
			}
			$products = $temp;

			$result = $this->productService->getOrderdProducts( $products, $category );

			$pdetails 		= array();
			$parent_details = array();
			$close_outs 	= array();
			$prices 		=  array();

			foreach ($result as $row) 
			{
				
				
				$parent_id = $row['parent_id'];
				
				if( !$parent_id )
				{
					if( strtolower($row['clearance']) == 'y' )
					{
						$close_outs[$row['id']] = $row;
					}
					else
					{
						$parent_details[$row['id']] = $row;
					}
					
					continue;
				}

				if( !$row['qty'] )
					continue;

				$combinations = explode(',', $row['combination']);
				
				$color_id 	= current($combinations);
				$size_id 	= next($combinations);
				
				$row['size_id'] = $size_id;

				$pdetails[$parent_id][$color_id][$size_id] = $row;

				$prices[$row['id']] = $row['price'];
				
			}

			$form = $this->getForm();			

			$request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
				$form->setData($_POST);

				$output = array('status' => 'success');
				
				if ($form->isValid()) 
				{
					$output['status'] = 'success';

					$data = $request->getPost();
					$ordered_products = $data['product'];
					unset($data['product']);

					//update order links data
					$data['categories'] = $orderlinks_data['categories'];
					$data['products'] 	= $orderlinks_data['products'];
					$this->productService->saveOrderLinks( array('id' => $id,'order_data' => json_encode($data)) );

					$ordered_product_prices = array();
					$temp = array();
					foreach ($ordered_products as $parent_id => $childs)
					{	
						$ec = 0;
						foreach ($childs as $child_id => $qty) 
						{
							if((int)$qty == 0)
								$ec++;

							$ordered_product_prices[$child_id] = $prices[$child_id];
						}

						if($ec == count($childs))
							unset($ordered_products[$parent_id]);
					}

					$cart_data = array();
					$cart_data['products'] = $ordered_products;
					$cart_data['prices'] = $ordered_product_prices;
										
					$order_id = $this->productService->saveOrder( array('order_link_id' => $id ,'cart_data' => json_encode($cart_data)) );
					
					$output['order_id'] = $order_id;

				}
				else
				{
					$m = $form->getMessages();
					$output['status'] = 'error';
					$output['errors'] = $m;
				}

				echo json_encode($output);die;
			}

			
			

			$form->setData($orderlinks_data);

			

			//echo '<pre>';print_r($parent_details);print_r($close_outs);die;
			return new ViewModel(array(
			 'server_url' => $this->server_url,	
             'id' => $id,
             'order_data' => (object)$orderlinks_data,
             'parent_details' => $parent_details,
             'pdetails' => $pdetails,
             'close_outs' => $close_outs,
             'options' => $options,
             'form' => $form
         	));

			
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

		function getForm()
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


			return $form;
		}




	}