<?php

// src/OC/PlateformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Skill;
use OC\PlatformBundle\Entity\AdvertSkill;




class AdvertController extends Controller
{
	public function indexAction($page)
	{
		$nbPerPages = 3;
		//Voir si le nombre de page n'est pas inférieur à 1
		if ($page < 1) {
			//On déclenche une exception NotFoundHttpException
			//La page 404 peut être modifiée
			throw new NotFoundHttpException("Page " . $page . " inexistante");
		}
		//On récupère la liste d'annonce et on l'a passe au template
		// Notre liste d'annonce en dur
		$listAdverts = $this
			->getDoctrine()
			->getManager()
			->getRepository("OCPlatformBundle:Advert")
			->getAdverts($page, $nbPerPages);
		$nbPages = ceil(count($listAdverts)/$nbPerPages);
		if ($page > $nbPages)
		{
			throw new NotFoundHttpException("Page demandé introuvable");
		}
		
		//On en fait que appeller le template
		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
				'listAdverts' => $listAdverts,
				'nbPages' => $nbPages,
				'page' => $page,
		));
		
	}
	public function viewAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		
		// On récupère l'annonce $id
		$advert = $em
		->getRepository('OCPlatformBundle:Advert')
		->find($id)
		;
		
		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}
		
		// On avait déjà récupéré la liste des candidatures
		$listApplications = $em
		->getRepository('OCPlatformBundle:Application')
		->findBy(array('advert' => $advert))
		;
		
		// On récupère maintenant la liste des AdvertSkill
		$listAdvertSkills = $em
		->getRepository('OCPlatformBundle:AdvertSkill')
		->findBy(array('advert' => $advert))
		;
		
		return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
				'advert'           => $advert,
				'listApplications' => $listApplications,
				'listAdvertSkills' => $listAdvertSkills
		));
	}
	
	public function addAction(Request $request) 
	{
		// On récupère l'EntityManager
		$em = $this->getDoctrine()->getManager();
		
		$advert = new Advert();
		$advert->setTitle('Recherche developpeur Symfony.');
		$advert->setAuthor('Alexandre');
		$advert->setContent('Nous sommes àla recherche d\'un developpeur symfony sur Lyon...');
		// On peut ne pas définir ni la date ni la publication,
		// car ces attributs sont définis automatiquement dans le constructeur
		
		//On récupère skills
		$listSkills = $em->getRepository("OCPlatformBundle:Skill")->findAll();
		//Pour chaque compétence
		foreach ($listSkills as $skill)
		{
			$advertSkill = new AdvertSkill();
			$advertSkill->setAdvert($advert);
			$advertSkill->setSkill($skill);
			$advertSkill->setLevel("Expert");
			$em->persist($advertSkill);
		}
		
		
		//Création de l'entité image
		$image = New Image();
		$image->setUrl("http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg");
		$image->setAlt("Job de rêve");
		
		//On lie l'image à l'annonce
		$advert->setImage($image);
		
		//Création d'une candidature
		$application1 = new Application();
		$application1->setAuthor('Marine');
		$application1->setContent('J\'ai toutes les qualités requises');
		
		//Création d'une candidature
		$application2 = new Application();
		$application2->setAuthor('Pierre');
		$application2->setContent('Et moi aussi');
		
		//Association des condidature à un objet advert
		$application1->setAdvert($advert);
		$application2->setAdvert($advert);
		
		
		// Étape 1 : On « persiste » l'entité
		$em->persist($advert);
		
		// Etape 1 bis, on persiste également les applications car il n'y a pas de cascade...
		$em->persist($application1);
		$em->persist($application2);
		// Étape 2 : On « flush » tout ce qui a été persisté avant
		$em->flush();
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
		$em = $this->getDoctrine()->getManager();
		// Ici, on récupérera l'annonce correspondante à $id
		$advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);
		
		if (null === $advert)
		{
			throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas !!!!!");
		}
		
		// Même mécanisme que pour l'ajout
		if ($request->isMethod('POST')) {
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
			
			return $this->redirectToRoute('oc_platform_view', array('id' => 5));
		}
		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
				'advert' => $advert
		));
	}
	
	public function deleteAction($id) 
	{
		//On récup l'annonce avec l'id
		$em = $this->getDoctrine()->getManager();
		$advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);
		if (null === $advert)
		{
			throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas !!!!!");
		}
		
		//On boucle pour enlever les catégories à l'annonce
		foreach ($advert->getCategories() as $category)
		{
			$advert->removeCategory($category);
		}
		
		//On persiste les changements
		$em->flush();
		
		//On supprime l'annonce
		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
	}
	public function viewSlugAction($slug, $year, $_format)
	{
		$content = "On pourrait afficher l'annonce correspondant au
		slug " . $slug . ", créée en " . $year . " et au format " . $_format;
		return new Response($content);
	}
	public function testFindAll()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository("OCPlatformBundle:Advert");
		
		$listAdverts = $repository->myFindAll();
		
		return $listAdverts;
	}
	public function menuAction($limit)
	{
		// On fixe en dur une liste ici, bien entendu par la suite
		// on la récupérera depuis la BDD !
		$listAdverts = $this
			->getDoctrine()
			->getManager()
			->getRepository("OCPlatformBundle:Advert")
			->findBy(
				array(),
				array('date' => 'desc'),
				$limit,
				0
			);
		
		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
				// Tout l'intérêt est ici : le contrôleur passe
				// les variables nécessaires au template !
				'listAdverts' => $listAdverts
		));
	}
}
