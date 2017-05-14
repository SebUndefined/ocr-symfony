<?php

// src/OC/PlateformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class AdvertController extends Controller
{
	public function indexAction($page)
	{
		//Voir si le nombre de page n'est pas inférieur à 1
		if ($page < 1) {
			//On déclenche une exception NotFoundHttpException
			//La page 404 peut être modifiée
			throw new NotFoundHttpException("Page " . $page . " inexistante");
		}
		//On récupère la liste d'annonce et on l'a passe au template
		// Notre liste d'annonce en dur
		$listAdverts = array(
				array(
						'title'   => 'Recherche développpeur Symfony',
						'id'      => 1,
						'author'  => 'Alexandre',
						'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
						'date'    => new \Datetime()),
				array(
						'title'   => 'Mission de webmaster',
						'id'      => 2,
						'author'  => 'Hugo',
						'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
						'date'    => new \Datetime()),
				array(
						'title'   => 'Offre de stage webdesigner',
						'id'      => 3,
						'author'  => 'Mathieu',
						'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
						'date'    => new \Datetime())
		);
		//On en fait que appeller le template
		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
				'listAdverts' => $listAdverts
		));
		
	}
	public function viewAction($id)
	{
		//Ici, on récup l'annonce en particulier
		
		$advert = array(
				'title'   => 'Recherche développpeur Symfony2',
				'id'      => $id,
				'author'  => 'Alexandre',
				'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
				'date'    => new \Datetime()
		);
		
		return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
				'advert' => $advert
		));
	}
	
	public function addAction(Request $request) 
	{
		
		//Si la requête est en POST, c'est que le visiteur a soumis le formulaire. 
		if ($request->isMethod('POST'))
		{
			//Création annonce + gestion formulaire
			//Puis ==>
			//Ajout des messages flash
			$session = $request->getSession();
			$session->getFlashBag()->add('info', 'Annonce bien enregistrée');
			//Redirection vers la page d'édition de l'annonce ajoutée
			return $this->redirectToRoute('oc_platform_view', array('id'=>5));
		}
		//Sinon, on affiche le template du formulaire simplement
		return $this->render('OCPlatformBundle:Advert:add.html.twig');
	}
	
	public function editAction($id, Request $request) 
	{
		// Ici, on récupérera l'annonce correspondante à $id
		
		// Même mécanisme que pour l'ajout
		if ($request->isMethod('POST')) {
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
			
			return $this->redirectToRoute('oc_platform_view', array('id' => 5));
		}
		
		$advert = array(
				'title'   => 'Recherche développpeur Symfony',
				'id'      => $id,
				'author'  => 'Alexandre',
				'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
				'date'    => new \Datetime()
		);
		
		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
				'advert' => $advert
		));
	}
	
	public function deleteAction($id) 
	{
		//On récup l'annonce avec l'id
		
		//On supprime l'annonce
		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
	}
	public function viewSlugAction($slug, $year, $_format)
	{
		$content = "On pourrait afficher l'annonce correspondant au
		slug " . $slug . ", créée en " . $year . " et au format " . $_format;
		return new Response($content);
	}
	public function menuAction($limit)
	{
		// On fixe en dur une liste ici, bien entendu par la suite
		// on la récupérera depuis la BDD !
		$listAdverts = array(
				array('id' => 2, 'title' => 'Recherche développeur Symfony'),
				array('id' => 5, 'title' => 'Mission de webmaster'),
				array('id' => 9, 'title' => 'Offre de stage webdesigner')
		);
		
		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
				// Tout l'intérêt est ici : le contrôleur passe
				// les variables nécessaires au template !
				'listAdverts' => $listAdverts
		));
	}
}
