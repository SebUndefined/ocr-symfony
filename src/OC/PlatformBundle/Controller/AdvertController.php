<?php

// src/OC/PlateformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdvertController extends Controller
{
	public function indexAction()
	{
		$url = $this
		->get('router')
		->generate(
				'oc_platform_view',
				array('id'=>6),
				UrlGeneratorInterface::ABSOLUTE_URL
				);
		return new Response("l'URL de la route avec id 6 est " . $url);
	}
	public function viewAction($id)
	{
		return new Response("Affichage de l'annonde d'id " . $id);
	}
	
	public function viewSlugAction($slug, $year, $_format)
	{
		$content = "On pourrait afficher l'annonce correspondant au
		slug " . $slug . ", créée en " . $year . " et au format " . $_format;
		return new Response($content);
	}
}