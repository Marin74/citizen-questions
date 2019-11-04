<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\ElectoralList;
use AppBundle\Entity\Answer;

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
        $repoQuestion = $em->getRepository("AppBundle:Question");
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        $questions = $repoQuestion->findBy(array("city" => null));
        
        if($request->getMethod() == "POST") {
            //die("ok");
            
            $city = $repoCity->findOneByInsee($request->get("city"));
            
            $electoralList = new ElectoralList();
            $electoralList->setName(trim($request->get("name")));
            $electoralList->setFirstnameHeadOfList1(trim($request->get("firstnameHeadOfList1")));
            $electoralList->setLastnameHeadOfList1(trim($request->get("lastnameHeadOfList1")));
            $electoralList->setFirstnameHeadOfList2(trim($request->get("firstnameHeadOfList2")));
            $electoralList->setLastnameHeadOfList2(trim($request->get("lastnameHeadOfList2")));
            $electoralList->setSupportedBy(trim($request->get("supportedBy")));
            $electoralList->setContactFirstname(trim($request->get("contactFirstname")));
            $electoralList->setContactLastname(trim($request->get("contactLastname")));
            $electoralList->setContactEmail(trim($request->get("contactEmail")));
            $electoralList->setContactPhone(trim($request->get("contactPhone")));
            $electoralList->setContactRole(trim($request->get("contactRole")));
            $electoralList->setCity($city);
            
            $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $confirmationCode = "";
            for ($i = 0; $i < 32; $i++) {
                $confirmationCode .= $characters[rand(0, strlen($characters) - 1)];
            }
            $electoralList->setConfirmationCode($confirmationCode);
            
            $em->persist($electoralList);
            
            foreach($questions as $question) {
                $answer = new Answer();
                $answer->setQuestion($question);
                $answer->setList($electoralList);
                $answer->setText(trim($request->get("text_".$question->getId())));
                
                if($request->get("radio_inProgram_".$question->getId()) == "true") {
                    $answer->setInProgram(true);
                    
                    if($request->get("radio_isPriority_".$question->getId()) == "true") {
                        $answer->setIsPriority(true);
                    }
                    else {
                        $answer->setIsPriority(false);
                    }
                }
                else {
                    $answer->setIsPriority(false);
                }
                
                $em->persist($answer);
            }
            
            foreach($city->getQuestions() as $question) {
                $answer = new Answer();
                $answer->setQuestion($question);
                $answer->setList($electoralList);
                $answer->setText(trim($request->get("text_".$question->getId())));
                
                if($request->get("radio_inProgram_".$question->getId()) == "true") {
                    $answer->setInProgram(true);
                    
                    if($request->get("radio_isPriority_".$question->getId()) == "true") {
                        $answer->setIsPriority(true);
                    }
                    else {
                        $answer->setIsPriority(false);
                    }
                }
                else {
                    $answer->setIsPriority(false);
                }
                
                $em->persist($answer);
            }
            
            $em->flush();
            
            // TODO Send email with confirmation code
        }
        
        return $this->render('default/answer_form.html.twig', [
            "cities"    => $cities,
            "questions" => $questions
        ]);
    }
    
    /**
     * @Route("/questions/ville/{insee}", name="answer_form_questions_of_city")
     */
    public function answerFormQuestionsOfCityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        
        $city = $repoCity->findOneByInsee($request->get("insee"));
        
        $questions = array();
        
        foreach($city->getQuestions() as $question) {
            $questions[] = array(
                "id"    => $question->getId(),
                "text"  => $question->getText()
            );
        }
        
        $params = array(
            "questions"	=> $questions
        );
        
        $response = new Response(json_encode($params));
        $response->headers->set("Content-Type", "application/json");
        return $response;
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
