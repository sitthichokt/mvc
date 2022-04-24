<?php 

namespace App\Controllers;
use functions;
use App\Models\Product;
use Symfony\Component\Routing\RouteCollection;

class PageController
{
    // Homepage action
	public function indexAction(RouteCollection $routes)
	{
		$routeToProduct = str_replace('{id}', 1, $routes->get('product')->getPath());
		$routeToProduct = str_replace('{ids}', 2, $routeToProduct);		
        view('home',$routeToProduct);
		
	}
}