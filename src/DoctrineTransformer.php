<?php

/**
 * ItePHP: Framework PHP (http://itephp.com)
 * Copyright (c) NewClass (http://newclass.pl)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the file LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) NewClass (http://newclass.pl)
 * @link          http://itephp.com ItePHP Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace ItePHP\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use ItePHP\Component\Form\Transformer;

/**
 * Map entity to form.
 *
 * Class DoctrineDesigner
 * @package ItePHP\Doctrine\Form
 */
class DoctrineTransformer implements Transformer{

    /**
     * @var string
     */
	private $entityName;

    /**
     * @var DoctrineService
     */
	private $doctrineService;

    /**
     * @var object
     */
	private $entity;

	/**
	 * @param DoctrineService $doctrineService
	 * @param string $entityName
	 */
	public function __construct(DoctrineService $doctrineService,$entityName){
		$this->entityName=$entityName;
		$this->doctrineService=$doctrineService;
	}

    /**
     * {@inheritdoc}
     */
	public function encode($entity){
		$this->entity=$entity;

		$values=[];
		foreach(get_class_methods($entity) as $method){
			if(preg_match('/^get(.*)$/',$method,$finds)){
				$data=$entity->$method();
				if(is_object($data)){

					if(isset($mapped[get_class($data)]))
						$data=$data->$mapped[get_class($data)]();
					else if(method_exists($data, 'getId'))
						$data=$data->getId();
					else if($data instanceof \DateTime)
						$data=$data->format('Y-m-d');
					else if($data instanceof PersistentCollection){
						$records=array();
						foreach($data as $record){
							$records[]=$record->getId();
						}
						$data=$records;
					}
					else
						continue;
				}


				$values[lcfirst($finds[1])]=$data;
			}
		}

		return $values;

	}

    /**
     * {@inheritdoc}
     */
	public function decode($data){
		$entity=$this->entity;
		if(!$entity){
			$entity=new $this->entityName;
		}

		$metaData=$this->doctrineService->getEntityManager()->getClassMetadata($this->entityName);
		
		foreach($data as $kData=>$vData){

			$value=$vData;
			if($metaData->hasAssociation($kData)){
				$this->decodeAssociation($metaData,$entity,$kData,$value);				
			}
			else if($metaData->hasField($kData) || method_exists($entity, 'set'.ucfirst($kData))){
				$this->decodeField($entity,$kData,$value);
			}
	
		}

		return $entity;

		
	}

    /**
     * @param object $entity
     * @param string $key
     * @param mixed $value
     */
	public function decodeField($entity,$key,$value){
		$methodName='set'.ucfirst($key);
		$entity->$methodName($value);
	}

    /**
     * @param ClassMetadata $metaData
     * @param object $entity
     * @param string $key
     * @param mixed $value
     */
	private function decodeAssociation($metaData,$entity,$key,$value){
		if($metaData->isSingleValuedAssociation($key)){
			$this->decodeAssociationSingle($metaData,$entity,$key,$value);
		}
		else{//multiple
			$this->decodeAssociationMulti($metaData,$entity,$key,$value);
		}

	}

    /**
     * @param ClassMetadata $metaData
     * @param object $entity
     * @param string $key
     * @param mixed $value
     */
	private function decodeAssociationSingle($metaData,$entity,$key,$value){
		if($value==''){
			$value=null;
		}
		else{
			$targetEntityName=$metaData->getAssociationTargetClass($key);
			$value=$this->doctrineService->getRepository($targetEntityName)->findOneBy(['id'=>$value]);
		}

		$methodName='set'.ucfirst($key);
		$entity->$methodName($value);

	}

    /**
     * @param ClassMetadata$metaData
     * @param object $entity
     * @param string $key
     * @param mixed $value
     */
	private function decodeAssociationMulti($metaData,$entity,$key,$value){
		$methodName='get'.$key;
        /**
         * @var ArrayCollection $collection
         */
		$collection=$entity->$methodName();
		$collection->clear();
		if(!$value){
			return;
		}

		$targetEntityName=$metaData->getAssociationTargetClass($key);
		foreach($value as $record){
			$value=$this->doctrineService->getRepository($targetEntityName)->findOneBy(['id'=>$record]);
			$collection->add($value);
		}
	}

}