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
use ItePHP\Contener\ServiceConfig;
use Doctrine\DBAL\Event\Listeners\MysqlSessionInit;
use ItePHP\Core\EventManager;

class Service{
	
	private $entityManager;

	public function __construct(ServiceConfig $serviceConfig,EventManager $eventManager){

		$paths = array();
		$isDevMode = true;
		$eventHandler=new EventHandler($eventManager);

		// the connection configuration
		$dbParams = array(
		    'driver'   => $serviceConfig->get('driver'),
		    'user'     => $serviceConfig->get('user'),
		    'password' => $serviceConfig->get('password'),
		    'dbname'   => $serviceConfig->get('dbname'),
		    'host'   => $serviceConfig->get('host'),
	        'charset' => 'utf8',
	        'driverOptions' => array(1002=>'SET NAMES utf8')

		);

		$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode,ITE_ROOT.'/cache');
		$this->entityManager = EntityManager::create($dbParams, $config);

		$this->entityManager->getEventManager()->addEventListener(array('onFlush'), $eventHandler);
		$this->entityManager->getEventManager()->addEventListener(array('postFlush'), $eventHandler);
	}

	public function getEntityManager(){
		return $this->entityManager;
	}

	public function getRepository($class){
		return $this->getEntityManager()->getRepository($class);
	}
}