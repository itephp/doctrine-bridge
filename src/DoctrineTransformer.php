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

use ItePHP\Component\Form\Transformer;
use ItePHP\Component\Form\FormBuilder;
use ItePHP\Component\Form\TextField;
use ItePHP\Component\Form\TextareaField;
use ItePHP\Component\Form\NumberField;
use ItePHP\Component\Form\SelectField;
use ItePHP\Component\Form\CheckboxField;
use ItePHP\Component\Form\DateField;

use Doctrine\ORM\Mapping\ClassMetadata;

use ItePHP\Doctrine\Exception\DoctrineTypeNotSupportedException;

/**
 * Map entity to form.
 *
 * Class DoctrineDesigner
 * @package ItePHP\Doctrine\Form
 */
class DoctrineTransformer implements Transformer{

	private $entityName;
	private $doctrineService;
	private $entity;
	/**
	 * @param \ItePHP\Doctrine\Service\Doctrine $doctrineService
	 * @param string $entityName
	 * @param array $filter
	 * @since 0.22.0
	 */
	public function __construct($doctrineService,$entityName){
		$this->entityName=$entityName;
		$this->doctrineService=$doctrineService;
	}

    /**
     * {@inheritdoc}
     */
	public function encode($entity){
		$this->entity=$entity;

		$values=array();
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
					else if($data instanceof \Doctrine\ORM\PersistentCollection){
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

	public function decodeField($entity,$key,$value){
		$methodName='set'.ucfirst($key);
		$entity->$methodName($value);
	}

	private function decodeAssociation($metaData,$entity,$key,$value){
		if($metaData->isSingleValuedAssociation($key)){
			$this->decodeAssociationSingle($metaData,$entity,$key,$value);
		}
		else{//multiple
			$this->decodeAssociationMulti($metaData,$entity,$key,$value);
		}

	}

	private function decodeAssociationSingle($metaData,$entity,$key,$value){
		if($value==''){
			$value=null;
		}
		else{
			$targetEntityName=$metaData->getAssociationTargetClass($key);
			$value=$this->doctrineService->getRepository($targetEntityName)->findOneById($value);
		}

		$methodName='set'.ucfirst($key);
		$entity->$methodName($value);

	}

	private function decodeAssociationMulti($metaData,$entity,$key,$value){
		$methodName='get'.$key;
		$collection=$entity->$methodName();
		$collection->clear();
		if(!$value){
			return;
		}

		$targetEntityName=$metaData->getAssociationTargetClass($key);
		foreach($value as $record){
			$value=$this->doctrineService->getRepository($targetEntityName)->findOneById($record);
			$collection->add($value);
		}

	}

}