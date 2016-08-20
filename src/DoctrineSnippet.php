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
use ItePHP\Core\Container;

class DoctrineSnippet {
	
	public function getDoctrine(Container $container){
		return $container->getService('doctrine');
	}

	public function getRepository(Container $container,$entity){
		return $container->getDoctrine()->getRepository('Entity\\'.$entity);
	}

	public function find(Container $container,$entity,$conditions=array(),$orders=array()){
		return $container->getRepository($entity)->findBy($conditions,$orders);
	}

	public function findOne(Container $container,$entity,$conditions=array(),$orders=array()){
		return $container->getRepository($entity)->findOneBy($conditions,$orders);
	}

	public function flush(Container $container){
		return $container->getDoctrine()->getEntityManager()->flush();
	}

	public function remove(Container $container,$entity){
		return $container->getDoctrine()->getEntityManager()->remove($entity);
	}

	public function createQuery(Container $container,$dql){
		return $container->getDoctrine()->getEntityManager()->createQuery($dql);
	}

	public function persist(Container $container,$entity){
		return $container->getDoctrine()->getEntityManager()->persist($entity);
	}

	public function executeQuery(Container $container,$sql,$parameters=array()){
		$stmt=$container->getDoctrine()->getEntityManager()->getConnection()->prepare($sql);
		$stmt->execute($parameters);
    	return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getConnection(Container $container){
		return $container->getDoctrine()->getEntityManager()->getConnection();
	}

	public function escape(Container $container,$value){
		return $container->getDoctrine()->getEntityManager()->getConnection()->quote($value);
	}	

}
