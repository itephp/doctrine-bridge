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

use ItePHP\Component\Form\Designer;
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
class DoctrineDesigner implements Designer{

	private $entityName;
	private $doctrineService;
	
	/**
	 * @param \ItePHP\Doctrine\Service\Doctrine $doctrineService
	 * @param string $entityName
	 * @param array $filter
	 * @since 0.18.0
	 */
	public function __construct($doctrineService,$entityName,$filter=null){
		$this->entityName=$entityName;
		$this->doctrineService=$doctrineService;
		$this->filter=$filter;
	}

    /**
     * {@inheritdoc}
     */
	public function build(FormBuilder $form){
		$metaData=$this->doctrineService->getEntityManager()->getClassMetadata($this->entityName);

		foreach($metaData->getFieldNames() as $fieldName){
			if(!$metaData->isIdentifier($fieldName) && (!$this->filter || in_array($fieldName, $this->filter))){
				$this->createField($form,$metaData,$fieldName);
			}
		}

		foreach($metaData->getAssociationNames() as $fieldName){
			if(!$metaData->isIdentifier($fieldName) && (!$this->filter || in_array($fieldName, $this->filter))){
				$this->createAssociationField($form,$metaData,$fieldName);
			}
		}

		
	}

	/**
	 * @param FormBuilder $form
	 * @param ClassMetadata $metaData
	 * @param string $fieldName
	 * @throws DoctrineTypeNotSupportedException
	 */
	public function createField($form,$metaData,$fieldName){
		$type=$metaData->getTypeOfColumn($fieldName);
		switch($type){
			case 'string':
				$formField=$this->createFieldString($metaData,$fieldName);
			break;
			case 'text':
				$formField=$this->createFieldText($metaData,$fieldName);
			break;
			case 'bigint':
			case 'integer':
				$formField=$this->createFieldInteger($metaData,$fieldName);
			break;
			case 'boolean':
				$formField=$this->createFieldBoolean($metaData,$fieldName);
			break;
			case 'datetime':
				$formField=$this->createFieldDateTime($metaData,$fieldName);
			break;
			case 'decimal':
				$formField=$this->createFieldDecimal($metaData,$fieldName);
			break;
			default:
				throw new DoctrineTypeNotSupportedException($type);
		}

		$formField->setLabel($this->translateName($fieldName));
		$formField->setName($fieldName);

		if(!$metaData->isNullable($fieldName)){
			$formField->setRequired(true);
		}


		$form->addField($formField);

	}

	/**
	 * @param FormBuilder $form
	 * @param ClassMetadata $metaData
	 * @param string $fieldName
	 */
	public function createAssociationField($form,$metaData,$fieldName){
		$formField=new SelectField(array());
		$formField->setLabel($this->translateName($fieldName));
		$formField->setName($fieldName);
		$mapping=$metaData->getAssociationMapping($fieldName);
		$required=false;


		$targetEntityName=$metaData->getAssociationTargetClass($fieldName);
		$targetEntity=$this->doctrineService->getRepository($targetEntityName);

		if($metaData->isCollectionValuedAssociation($fieldName)){
			$formField->setMultiple(true);
			$formField->setRequired(false);
		}
		else{
			foreach($mapping['joinColumns'] as $joinColumn){
				if(!$joinColumn['nullable']){
					$required=true;
					break;
				}
			}
			$formField->setRequired($required);

		}

		$collection=static::entityToCollection($targetEntity->findAll(),!$formField->isMultiple());
		$formField->setCollection($collection);


		$form->addField($formField);

	}

	public static function entityToCollection($entity,$appendEmptyRecord){
		$values=array();
		if($appendEmptyRecord){
			$values[]=array('value'=>'','label'=>'Select...');			
		}

		foreach($entity as $record){
			$values[]=array('value'=>$record->getId(),'label'=>htmlspecialchars($record->__toString()));
		}

		return $values;
	}

	private function createFieldString($metaData,$fieldName){
		$formField=new TextField(array());
		return $formField;
	}

	private function createFieldDateTime($metaData,$fieldName){
		$formField=new DateField(array());
		return $formField;
	}

	private function createFieldText($metaData,$fieldName){
		$formField=new TextareaField(array());
		return $formField;
	}

	private function createFieldBoolean($metaData,$fieldName){
		$formField=new CheckboxField(array());
		return $formField;
	}

	private function createFieldDecimal($metaData,$fieldName){
		$formField=new NumberField(array('step'=>'any'));
		return $formField;
	}

	private function createFieldInteger($metaData,$fieldName){
		if($metaData->isAssociationWithSingleJoinColumn($fieldName)){//references

		}
		else{//normal integer
			$formField=new NumberField(array());
		}

		return $formField;
	}

	private function translateName($fieldName) {
        $re = '/(?<=[a-z])(?=[A-Z])/x';
        $parts = preg_split($re, $fieldName);
        return ucfirst(join($parts, " "));
}
}