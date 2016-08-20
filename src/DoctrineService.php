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

use ItePHP\Doctrine\EventHandler;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use Doctrine\DBAL\Event\Listeners\MysqlSessionInit;

use ItePHP\Core\EventManager;
use ItePHP\Core\Enviorment;

class DoctrineService{
	
	/**
	 *
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 *
	 * @param Enviorment $enviorment
	 * @param EventManager $eventManager
	 * @param string $driver
	 * @param string $username
	 * @param string $password
	 * @param string $dbname
	 * @param string $host
	 * @param int $port
	 */
	public function __construct(Enviorment $enviorment,EventManager $eventManager,$driver,$username,$password,$dbname
		,$host,$port){

		$paths = array();
		$eventHandler=new EventHandler($eventManager);

		$dbParams = array(
		    'driver'   => $driver,
		    'user'     => $username,
		    'password' => $password,
		    'dbname'   => $dbname,
		    'host'   => $host,
	        'charset' => 'utf8',
		);

		$config = Setup::createAnnotationMetadataConfiguration($paths, $enviorment->isDebug(),$enviorment->getCachePath());
		$this->entityManager = EntityManager::create($dbParams, $config);

		$this->entityManager->getEventManager()->addEventListener(array('onFlush'), $eventHandler);
		$this->entityManager->getEventManager()->addEventListener(array('postFlush'), $eventHandler);
	}

	/**
	 *
	 * @return EntityManager
	 */
	public function getEntityManager(){
		return $this->entityManager;
	}

	/**
	 *
	 * @param string $class
	 * @return EntityManager
	 */
	public function getRepository($class){
		return $this->getEntityManager()->getRepository($class);
	}
}