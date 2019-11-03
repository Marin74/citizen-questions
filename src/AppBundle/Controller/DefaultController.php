<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\ElectoralList;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        $lists = $repoElectoralList->findBy(array("status" => ElectoralList::STATUS_VALIDATED), array("validationDate" => "DESC"), 10);
        
        return $this->render('default/index.html.twig', [
            "cities"    => $cities,
            "lists"     => $lists
        ]);
    }
    
    /**
     * @Route("/ville/{insee}/{name}", name="city")
     */
    public function cityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        $repoQuestion = $em->getRepository("AppBundle:Question");
        
        $city = $repoCity->findOneByInsee($request->get("insee"));
        $generalQuestions = $repoQuestion->findBy(array("city" => null));
        $cityQuestions = array();
        
        if($city != null) {
            $cityQuestions = $repoQuestion->findBy(array("city" => $city));
        }
        
        $questions = array_merge($generalQuestions, $cityQuestions);
        
        return $this->render('default/city.html.twig', [
            "city"      => $city,
            "questions" => $questions
        ]);
    }
    
    /**
     * @Route("/liste/{id}/{name}", name="list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        
        $list = $repoElectoralList->findOneBy(array("id" => $request->get("id"), "status" => ElectoralList::STATUS_VALIDATED));
        
        // replace this example code with whatever you need
        return $this->render('default/list.html.twig', [
            "list"  => $list
        ]);
    }
    
    /**
     * @Route("/repondre", name="answer_form")
     */
    public function answerFormAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        
        return $this->render('default/answerForm.html.twig', [
            "cities"    => $cities
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
