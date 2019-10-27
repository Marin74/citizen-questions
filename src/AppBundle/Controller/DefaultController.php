<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        
        $cities = $repoCity->findAll();
        
        return $this->render('default/index.html.twig', [
            "cities"    => $cities
        ]);
    }
    
    /**
     * @Route("/ville/{insee}/{name}", name="city")
     */
    public function cityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        
        $city = $repoCity->findOneByInsee($request->get("insee"));
        
        return $this->render('default/city.html.twig', [
            "city"  => $city
        ]);
    }
    
    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/contact.html.twig', [
        ]);
    }
    
    /**
     * @Route("/mentions-legales", name="terms")
     */
    public function termsAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/terms.html.twig', [
        ]);
    }
}
