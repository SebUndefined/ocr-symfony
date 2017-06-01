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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;




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
		$advert = new Advert();
		
		$form = $this->get('form.factory')->create(AdvertType::class, $advert);
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			//On vérifie la validité du formulaire
			if ($form->isValid()) {
				// Ajoutez cette ligne :
				// c'est elle qui déplace l'image là où on veut les stocker

				//On enregistre l'annonce
				$em = $this->getDoctrine()->getManager();
				$em->persist($advert);
				$em->flush();
				
				$request->getSession()->getFlashBag()->add('notice', 'l\'annonce est bien enregistré');
				
				//On redirige vers la vue
				return $this->redirectToRoute('oc_platform_view', array('id'=>$advert->getId()));
			}
		}
		
		
		//CreateView pour afficher le formulaire tout seul
		return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
			'form' => $form->createView()	
		));
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
        $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);
		// Même mécanisme que pour l'ajout
		if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
			
			return $this->redirectToRoute('oc_platform_view', array('id' => $id));
		}
		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
				'advert' => $advert,
				'form' => $form->createView()
		));
	}
	
	public function deleteAction(Request $request, $id)
	{
		//On récup l'annonce avec l'id
		$em = $this->getDoctrine()->getManager();
		$advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);
		if (null === $advert)
		{
			throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas !!!!!");
		}
		
		$form = $this->get('form.factory')->create();

		if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
		    $em->remove($advert);
		    $em->flush();
		    $request->getSession()->getFlashBag()->add('info', 'Annonce supprimé');
		    return $this->redirectToRoute('oc_platform_home');
        }

		return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
		    'advert' => $advert,
            'form' => $form->createView()
        ));
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
