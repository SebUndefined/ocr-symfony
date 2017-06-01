<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use OC\PlatformBundle\Repository\CategoryRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$pattern = 'D%';
        $builder
        ->add('date', DateTimeType::class)
        ->add('title', TextType::class)
        ->add('author', TextType::class)
        ->add('content', TextareaType::class)
        ->add('image', ImageType::class)
        ->add('categories', EntityType::class, array(
        		'class' => 'OCPlatformBundle:Category',
        		'choice_label' => 'name',
        		'multiple' => true,
        		'query_builder' => function(CategoryRepository $repository) use($pattern){
        			return $repository->getLikeQueryBuilder($pattern);
        		}	
        ))
        ->add('save', SubmitType::class);
        //On récupère notre objet Advert
        $builder->addEventListener(
        		FormEvents::PRE_SET_DATA, //evenement qui nous intéresse
        		function (FormEvent $event) {//fonction a executer
        			//We get the advert objet
        			$advert = $event->getData();
        			
        			if(null === $advert) {
        				return;
        			}
        			//si l'annonce n'est pas publié ou si elle n'a pas d'id (pas en base)
        			if(!$advert->getPublished() || null === $advert->getId()) {
        				//Alors on ajoute le champs published
        				$event->getForm()->add('published', CheckboxType::class, array('required' => false));
        			}
        			else {
        				//sinon, on supprime
        				$event->getForm()->remove('published');
        			}
        			
        });
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oc_platformbundle_advert';
    }


}
