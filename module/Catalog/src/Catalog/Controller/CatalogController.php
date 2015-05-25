<?php
	namespace Catalog\Controller;

	use Catalog\Service\ProductServiceInterface;
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;

	class CatalogController extends AbstractActionController
	{
		protected $productService;

		public function __construct(ProductServiceInterface $productService)
		{
			$this->productService = $productService;
		}

		public function indexAction()
		{
			return new ViewModel(array(
             'data' => $this->productService->findAllProducts()
         	));

		}

		public function addAction()
		{
		}

		public function editAction()
		{
		}

		public function deleteAction()
		{
		}
	}