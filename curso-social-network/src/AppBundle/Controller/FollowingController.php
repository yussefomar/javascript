<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Entity\User;
use AppBundle\Form\RegisterType;
use AppBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use BackendBundle\Entity\Following;
//use Symfony\Component\BrowserKit\Response;

class FollowingController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function followAction(Request $request){
        echo"seguir";
        die();
    }
}
