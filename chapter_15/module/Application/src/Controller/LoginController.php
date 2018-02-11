<?php

namespace Application\Controller;
use Application\Model;
use Zend\Session;
use Application\Form;

class LoginController extends AbstractController
{
    protected $securityAuth;

    public function __construct($securityAuth)
    {
        $this->securityAuth = $securityAuth;
    }
    
    public function indexAction()
    {
        $form = new Form\UserLoginForm();
        
        if (!$this->getRequest()->isPost()) {
            return [
                'form' => $form
            ];
        }
        $form->setData($this->getRequest()->getPost());
        
        if (!$form->isValid()) {
            return [
                'form' => $form,
                'messages' => $form->getMessages()
            ];
        }
        
        $auth = $this->securityAuth->authenticate(
            $form->get($form::FIELDSET_LOGIN)->get('email')->getValue(),
            $form->get($form::FIELDSET_LOGIN)->get('password')->getValue()
        );
        $identity = $this->securityAuth->getIdentityArray();

        if ($identity) {
            $rowset = new Model\Rowset\User();
            $rowset->exchangeArray($identity);
            
            $sessionUser = new Session\Container('user');
            $sessionUser->details = $rowset;

            return $this->redirect()->toRoute('login', ['action' => 'progressuser']);
        } else {
            $message = '<strong>Error</strong> Provided email address or password is incorrect.';
            
            return [
                'form' => $form,
                'messages' => $message
            ];
        }
    }
    
    public function progressUserAction()
    {   
        //$authObject = new Security\Authorization();
        //$sessionUser->availableResources = $authObject->getResourcesForPermission(NULL, $sessionUser->details['role'], true);
        $sessionUser = new Session\Container('user');
        $prefix = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->baseUrl;

        if ($_SERVER['HTTP_REFERER'] !== $prefix.'/register' &&
            $_SERVER['HTTP_REFERER'] !== $prefix.'/login'
        ) {
            return $this->redirect()->toUrl($_SERVER['HTTP_REFERER'], 302);
        }
        
        if ($sessionUser->details->getRole() === 'admin') {
            $this->redirect()->toRoute('admin', ['controller' => 'IndexController', 'action' => 'index']);
        } else if($sessionUser->details->getRole() === 'user') {
            $this->redirect()->toRoute('user');
        }
    }
    
    public function logoutAction()
    {
        $session = new Session\Container('user');
        $session->getManager()->destroy();
        $this->redirect()->toRoute('home');
    }
}
