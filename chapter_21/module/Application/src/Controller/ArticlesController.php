<?php
namespace Application\Controller;

class ArticlesController extends AbstractController
{
    private $cmsObject;
    
    public function __construct($cmsObject)
    {
         $this->cmsObject = $cmsObject;
    }
    public function indexAction()
    {
        return [];
    }
    
    public function showAction() {
        $pageDetails = $this->cmsObject->getArtcileContentByUrl($this->params()->fromRoute('id'));

        $this->getEvent()->getTarget()->layout()->title = $pageDetails[0]['title'];
        $this->getEvent()->getTarget()->layout()->description = $pageDetails[0]['description'];
        $this->getEvent()->getTarget()->layout()->keywords = $pageDetails[0]['keywords'];

        return [
            'page' => $pageDetails
        ];
    }
}
