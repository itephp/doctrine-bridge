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

use Doctrine\ORM\Mapping\ClassMetadata;
use Forma\Designer;
use Forma\Field\CheckboxField;
use Forma\Field\DateField;
use Forma\Field\NumberField;
use Forma\Field\SelectField;
use Forma\Field\TextareaField;
use Forma\Field\TextField;
use Forma\FormBuilder;

/**
 * Map entity to form.
 *
 * Class DoctrineDesigner
 * @package ItePHP\Doctrine\Form
 */
class DoctrineDesigner implements Designer{

    /**
     * @var string
     */
	private $entityName;

    /**
     * @var DoctrineService
     */
	private $doctrineService;

    /**
     * @var string[]
     */
    private $filter;

    /**
	 * @param DoctrineService $doctrineService
	 * @param string $entityName
	 * @param string[] $filter
	 */
	public function __construct(DoctrineService $doctrineService,$entityName,$filter=null){
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
		$type=$metaData->getTypeOfField($metaData->getFieldName($fieldName));
		switch($type){
			case 'string':
				$formField=$this->createFieldString();
			break;
			case 'text':
				$formField=$this->createFieldText();
			break;
			case 'bigint':
			case 'integer':
				$formField=$this->createFieldInteger($metaData,$fieldName);
			break;
			case 'boolean':
				$formField=$this->createFieldBoolean();
			break;
			case 'datetime':
				$formField=$this->createFieldDateTime();
			break;
			case 'decimal':
				$formField=$this->createFieldDecimal();
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
		$formField=new SelectField();
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

    /**
     * @param object[] $entity
     * @param bool $appendEmptyRecord
     * @return mixed[]
     */
	public static function entityToCollection($entity,$appendEmptyRecord){
		$values=[];
		if($appendEmptyRecord){
			$values[]=['value'=>'','label'=>'Select...'];
		}

        foreach($entity as $record){
            $values[]=['value'=>htmlspecialchars($record->getId()),'label'=>htmlspecialchars($record->__toString())];
        }

		return $values;
	}

    /**
     * @return TextField
     */
	private function createFieldString(){
		$formField=new TextField();
		return $formField;
	}

    /**
     * @return DateField
     */
	private function createFieldDateTime(){
		$formField=new DateField();
		return $formField;
	}

    /**
     * @return TextareaField
     */
	private function createFieldText(){
		$formField=new TextareaField();
		return $formField;
	}

    /**
     * @return CheckboxField
     */
	private function createFieldBoolean(){
		$formField=new CheckboxField();
		return $formField;
	}

    /**
     * @return NumberField
     */
	private function createFieldDecimal(){
		$formField=new NumberField(['step'=>'any']);
		return $formField;
	}

    /**
     * @param ClassMetadata $metaData
     * @param string $fieldName
     * @return NumberField
     */
	private function createFieldInteger($metaData,$fieldName){
        $formField=null;
		if(!$metaData->isAssociationWithSingleJoinColumn($fieldName)){
			$formField=new NumberField(array());
		}

		return $formField;
	}

    /**
     * @param string $fieldName
     * @return string
     */
	private function translateName($fieldName) {
        $re = '/(?<=[a-z])(?=[A-Z])/x';
        $parts = preg_split($re, $fieldName);
        return ucfirst(join($parts, " "));
    }
}