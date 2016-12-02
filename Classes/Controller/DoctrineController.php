<?php

namespace KayStrobach\Migrations\Controller;



use KayStrobach\Migrations\Service\DoctrineService;

class DoctrineController extends  \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var DoctrineService
     */
    protected $doctrineService;

    /**
     *
     */
    public function indexAction() {
        require_once __DIR__ . '/../../Resources/Private/Php/vendor/autoload.php';

        $this->doctrineService = $this->objectManager->get(
            DoctrineService::class
        );

        $this->view->assign('doctrineStatus', $this->doctrineService->getMigrationStatus());
    }
}