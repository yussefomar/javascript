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

//use Symfony\Component\BrowserKit\Response;

class UserController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function loginAction(Request $request) {
        if (is_object($this->getUser())) { /* el usuario ya esta logeado si va a login denuevo lo rederidigjo a la home */
            return $this->redirect('home');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('AppBundle:User:login.html.twig', array('last_username' => $lastUsername, 'error' => $error));
    }

    public function loginCheckAction() {
        
    }

    public function registerAction(Request $request) {

        if (is_object($this->getUser())) {
            return $this->redirect('home');
        }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email= :email OR u.nick= :nick')
                        ->setParameter('email', $form->get("email")->getData())
                        ->setParameter('nick', $form->get("nick")->getData()
                );
                $user_isset = $query->getResult();
                if (count($user_isset) == 0) {

                    $factory = $this->get("security.encoder_factory");
                    $encoder = $factory->getEncoder($user);
                    $password = $encoder->encodePassword($form->get("password")->getData(), $user->getSalt());

                    $user->setPassword($password);
                    $user->setRole("ROLE_USER");
                    $user->setImage(null);
                    $em->persist($user);
                    $flush = $em->flush($user);

                    if ($flush == null) {
                        $status = "esta correctamente registrado,Felicitaciones! ";
                        $this->session->getFlashBag()->add("status", $status);
                        return $this->redirect("login");
                    } else {
                        $status = "no te haz Registrado Correctamente";
                    }
                } else {

                    $status = "El email ya existe!";
                }
            } else {
                $status = "No te haz registrado adecuadamente";
            }
            $this->session->getFlashBag()->add("status", $status);
        }
        return $this->render('AppBundle:User:register.html.twig', array("form" => $form->createView()));
    }

    public function nickTestAction(Request $request) {

        $nick = $request->get("nick");

        $em = $this->getDoctrine()->getManager();
        $user_repo = $em->getRepository("BackendBundle:User");
        $user_isset = $user_repo->findOneBy(array("nick" => $nick));

        $result = "used";

        if (count($user_isset) >= 1 && is_object($user_isset)) {
            $result = "used";
        } else {
            $result = "unused";
        }

        return new Response($result);
//  return new JsonResponse(
// [
//   'result' => $result 
// ]
//);
    }

    public function editUserAction(Request $request) {
        $user = $this->getUser();
        $form = $this->createForm(UserType::Class, $user);
        $form->handleRequest($request);
        $user_image = $user->getImage();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email= :email OR u.nick= :nick')
                        ->setParameter('email', $form->get("email")->getData())
                        ->setParameter('nick', $form->get("nick")->getData()
                );
                $user_isset = $query->getResult();
                if ( count($user_isset) == 0 || $user->getEmail() == $user_isset[0]->getEmail() && $user->getNick() == $user_isset[0]->getNick() ) {

                    $file = $form["image"]->getData();

                    if (!empty($file) && $file != null) {
                        $ext = $file->guessExtension();
                        if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                            $file_name = $user->getId() . time() . '.' . $ext;
                            $file->move("uploads/users", $file_name);
                            $user->setImage($file_name);
                        }
                    } else {
                        $user->setImage($user_image);
                    }


                    $em->persist($user);
                    $flush = $em->flush($user);

                    if ($flush == null) {
                        $status = "haz modificado tus datos correctamente,Felicitaciones! ";
                    } else {
                        $status = "no haz modificado tus datos  Correctamente";
                    }
                } else {

                    $status = "El usuario ya existe!";
                }
            } else {
                $status = "No se han actualiado tus datos adecuadamente";
            }
            $this->session->getFlashBag()->add("status", $status);
            return $this->redirect('my-data');
        }

        return $this->render('AppBundle:User:edit_user.html.twig', array('form' => $form->createView()));
    }

    public function usersAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT u FROM BackendBundle:User u ORDER BY u.id ASC";
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, /* query NOT result */ $request->query->getInt('page', 1)/* page number */, 5/* limit per page , 5 suuarios por pagina */
        );

// parameters to template
        return $this->render('AppBundle:User:users.html.twig', array('pagination' => $pagination));
    }

    
     public function searchAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $search=$request->query->get("search",null);
        
        if($search==null){
            return $this->redirect($this->generateUrl('publications_home'));
        }
        $dql = "SELECT u FROM BackendBundle:User u "
                ."WHERE u.name LIKE :search OR u.surname LIKE :search"
                ." OR u.nick LIKE :search ORDER BY u.id ASC";
        
        
        $query = $em->createQuery($dql)->setParameter('search',"%$search%");
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, /* query NOT result */ $request->query->getInt('page', 1)/* page number */, 5/* limit per page , 5 suuarios por pagina */
        );

// parameters to template
        return $this->render('AppBundle:User:users.html.twig', array('pagination' => $pagination));
    }
}
