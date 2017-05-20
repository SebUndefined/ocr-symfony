<?php 

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Category;
use Doctrine\Common\DataFixtures\FixtureInterface;

class LoadCategory implements FixtureInterface
{
	public function load(ObjectManager $manager) 
	{
		// Liste des noms de catégorie à ajouter
		$names = array(
				'Développement web',
				'Développement mobile',
				'Graphisme',
				'Intégration',
				'Réseau'
		);
		
		foreach ($names as $name) {
			// On crée la catégorie
			$category = new Category();
			$category->setName($name);
			
			// On la persiste
			$manager->persist($category);
		}
		
		// On déclenche l'enregistrement de toutes les catégories
		$manager->flush();
	
	}
}