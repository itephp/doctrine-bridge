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

use Doctrine\Common\Persistence\ObjectRepository;
use ItePHP\Core\Container;

/**
 * Class DoctrineSnippet
 * @package ItePHP\Doctrine
 */
class DoctrineSnippet {

    /**
     * @param Container $container
     * @return DoctrineService
     */
	public function getDoctrine(Container $container){
		return $container->getService('doctrine');
	}

    /**
     * @param Container $container
     * @param $entity
     * @return ObjectRepository
     */
	public function getRepository(Container $container,$entity){
		return $this->getDoctrine($container)->getRepository('Entity\\'.$entity);
	}

    /**
     * @param Container $container
     * @param $entity
     * @param array $conditions
     * @param array $orders
     * @return array
     */
	public function find(Container $container,$entity,$conditions=array(),$orders=array()){
		return $this->getRepository($container,$entity)->findBy($conditions,$orders);
	}

    /**
     * @param Container $container
     * @param $entity
     * @param array $conditions
     * @param array $orders
     * @return array
     */
	public function findOne(Container $container,$entity,$conditions=array(),$orders=array()){
		return $this->getRepository($container,$entity)->findBy($conditions,$orders,1);
	}

    /**
     * @param Container $container
     */
	public function flush(Container $container){
		return $this->getDoctrine($container)->getEntityManager()->flush();
	}

    /**
     * @param Container $container
     * @param $entity
     */
	public function remove(Container $container,$entity){
		return $this->getDoctrine($container)->getEntityManager()->remove($entity);
	}

    /**
     * @param Container $container
     * @param $dql
     * @return \Doctrine\ORM\Query
     */
	public function createQuery(Container $container,$dql){
		return $this->getDoctrine($container)->getEntityManager()->createQuery($dql);
	}

    /**
     * @param Container $container
     * @param $entity
     */
	public function persist(Container $container,$entity){
		return $this->getDoctrine($container)->getEntityManager()->persist($entity);
	}

    /**
     * @param Container $container
     * @param $sql
     * @param array $parameters
     * @return array
     */
	public function executeQuery(Container $container,$sql,$parameters=array()){
		$stmt=$this->getDoctrine($container)->getEntityManager()->getConnection()->prepare($sql);
		$stmt->execute($parameters);
    	return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

    /**
     * @param Container $container
     * @return \Doctrine\DBAL\Connection
     */
	public function getConnection(Container $container){
		return $this->getDoctrine($container)->getEntityManager()->getConnection();
	}

    /**
     * @param Container $container
     * @param $value
     * @return string
     */
	public function escape(Container $container,$value){
		return $this->getDoctrine($container)->getEntityManager()->getConnection()->quote($value);
	}	

}

