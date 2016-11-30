<?php 
class ControllerProductAllCategories extends Controller {  
	public function index() { 
		$this->language->load('product/category');
		
		$this->load->model('catalog/category');
				
		$this->load->model('catalog/product');

		$this->load->model('tool/image');
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name'; // sort_orders'
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else { 
			$page = 1;
		}	
							
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}
							
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
       		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_all_categories'),
			'href'      => $this->url->link('product/allcategories'),
			'separator' => $this->language->get('text_separator')
   		);
   		
		$this->document->setTitle($this->language->get('text_all_categories'));
		$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');
		
		$this->data['heading_title'] = $this->language->get('text_all_categories');
		
		$this->data['text_refine'] = $this->language->get('text_refine');
		$this->data['text_empty'] = $this->language->get('text_empty');			
		$this->data['text_quantity'] = $this->language->get('text_quantity');
		$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
		$this->data['text_model'] = $this->language->get('text_model');
		$this->data['text_price'] = $this->language->get('text_price');
		$this->data['text_tax'] = $this->language->get('text_tax');
		$this->data['text_points'] = $this->language->get('text_points');
		$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		$this->data['text_display'] = $this->language->get('text_display');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_limit'] = $this->language->get('text_limit');
		$this->data['text_index'] = $this->language->get('text_index');
		
		$this->data['text_all_categories'] = $this->language->get('text_all_categories');
				
		$this->data['button_cart'] = $this->language->get('button_cart');
		$this->data['button_wishlist'] = $this->language->get('button_wishlist');
		$this->data['button_compare'] = $this->language->get('button_compare');
		$this->data['button_continue'] = $this->language->get('button_continue');
			
		$this->data['compare'] = $this->url->link('product/compare');
		
		$url = '';
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}	
		
		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
							
		$this->data['indexes'] = array();

		$data = array(
			'filter_status'		 => 1,
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);
		
		$categories_total = $this->model_catalog_category->getTotalCategories($data);
		
		$categories = $this->model_catalog_category->getAllCategories($data);
							
		foreach ($categories as $category) {			
			switch($sort) {
				case 'name':
					$name = $category['name'];
					break;
				case 'path_name':
					$name = $category['path_name'];
					break;
				case 'sort_order':
					$name = $category['name'];
					break;
				case 'sort_order_path':
					$name = $category['path_name'];
					break;
				default:
					$name = $category['name'];						
			}
			
			if ($category['image']) {
				$image = $this->model_tool_image->resize($category['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
			}

			$data = array(
				'filter_category_id' => $category['category_id'],
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($data); 
			
			if (strpos($sort, 'name') === false) {
				$this->data['categories'][] = array(
					'name'  => $name . ($this->config->get('config_product_count') ? ' (' . $product_total . ')' : ''),
					'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . $url),
					'thumb'	=> $image
				);
			} else {
				if (is_numeric(utf8_substr($name, 0, 1))) {
					$key = '0 - 9';
				} else {
					$key = utf8_substr(utf8_strtoupper($name), 0, 1);
				}
				
				if (!isset($this->data['indexes'][$key])) {
					$this->data['indexes'][$key]['name'] = $key;
					$this->data['indexes'][$key]['href'] = $this->url->link('product/allcategories', $url);
				}

				$this->data['indexes'][$key]['category'][] = array(
					'name'  => $name . ($this->config->get('config_product_count') ? ' (' . $product_total . ')' : ''),
					'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . $url),
					'thumb'	=> $image
				);
			}
		}
	
		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
						
		$this->data['sorts'] = array();
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'name-ASC',
			'href'  => $this->url->link('product/allcategories','&sort=name&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'name-DESC',
			'href'  => $this->url->link('product/allcategories','&sort=name&order=DESC' . $url)
		);
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_path_name_asc'),
			'value' => 'path_name-ASC',
			'href'  => $this->url->link('product/allcategories','&sort=path_name&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_path_name_desc'),
			'value' => 'path_name-DESC',
			'href'  => $this->url->link('product/allcategories','&sort=path_name&order=DESC' . $url)
		);
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_sort_order_asc'),
			'value' => 'sort_order-ASC',
			'href'  => $this->url->link('product/allcategories','&sort=sort_order&order=ASC' . $url)
		);
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_sort_order_desc'),
			'value' => 'sort_order-DESC',
			'href'  => $this->url->link('product/allcategories','&sort=sort_order&order=DESC' . $url)
		);
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_sort_order_path_asc'),
			'value' => 'sort_order_path-ASC',
			'href'  => $this->url->link('product/allcategories','&sort=sort_order_path&order=ASC' . $url)
		);
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_sort_order_path_desc'),
			'value' => 'sort_order_path-DESC',
			'href'  => $this->url->link('product/allcategories','&sort=sort_order_path&order=DESC' . $url)
		);
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		$this->data['limits'] = array();
		
		$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $value){
			$this->data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('product/allcategories', $url . '&limit=' . $value)
			);
		}
					
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
				
		$pagination = new Pagination();
		$pagination->total = $categories_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('product/allcategories',$url . '&page={page}');
	
		$this->data['pagination'] = $pagination->render();
	
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;
	
		$this->data['continue'] = $this->url->link('common/home');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/allcategories.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/product/allcategories.tpl';
		} else {
			$this->template = 'default/template/product/allcategories.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
			
		$this->response->setOutput($this->render());
  	}
}
?>