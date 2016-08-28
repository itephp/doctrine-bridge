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

use ItePHP\Core\EventManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Class EventHandler
 * @package ItePHP\Doctrine
 */
class EventHandler{

    /**
     * @var EventManager
     */
	private $eventManager;

    /**
     * EventHandler constructor.
     * @param EventManager $eventManager
     */
	public function __construct(EventManager $eventManager){
		$this->eventManager=$eventManager;
	}

    /**
     * @param OnFlushEventArgs $eventArgs
     */
	public function onFlush(OnFlushEventArgs $eventArgs){
		$this->eventManager->fire('doctrine.onFlush',$eventArgs);

	}

    /**
     * @param PostFlushEventArgs $eventArgs
     */
	public function postFlush(PostFlushEventArgs $eventArgs){
		$this->eventManager->fire('doctrine.postFlush',$eventArgs);

	}

}
