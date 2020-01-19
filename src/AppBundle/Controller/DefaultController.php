<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\Answer;
use AppBundle\Entity\ElectoralList;
use AppBundle\Entity\OriginalQuestion;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoCity = $em->getRepository("AppBundle:City");
        $repoGroupOfCities = $em->getRepository("AppBundle:GroupOfCities");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        $groupsOfCities = $repoGroupOfCities->findBy(array(), array("name" => "ASC"));
        $lists = $repoElectoralList->findBy(array("status" => ElectoralList::STATUS_VALIDATED), array("validationDate" => "DESC"), 10);
        
        if($request->getMethod() == "POST") {
            $originalQuestion = new OriginalQuestion();
            $originalQuestion->setText(trim($request->get("text")));
            
            // Cities
            $tempCities = array();
            
            foreach($cities as $city) {
                if($request->get("city_".$city->getId()) == "".$city->getId()) {
                    $tempCities[] = $city;
                }
            }
            
            if(count($tempCities) < count($cities)) {
                $originalQuestion->setCities($tempCities);
            }
            
            // Groups of cities
            $tempGroupsOfCities = array();
            
            foreach($groupsOfCities as $groupOfCities) {
                if($request->get("groupOfCities_".$groupOfCities->getId()) == "".$groupOfCities->getId()) {
                    $tempGroupsOfCities[] = $groupOfCities;
                }
            }
            
            if(count($tempGroupsOfCities) < count($groupsOfCities)) {
                $originalQuestion->setGroupsOfCities($tempGroupsOfCities);
            }
            
            $em->persist($originalQuestion);
            $em->flush();
            
            $this->addFlash(
                'success',
                $translator->trans("ask_question_succeeded")
            );
        }
        
        return $this->render('default/index.html.twig', [
            "cities"            => $cities,
            "groupsOfCities"    => $groupsOfCities,
            "lists"             => $lists
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
    public function answerFormAction(Request $request, \Swift_Mailer $mailer)
    {
        // TODO Supprimer
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        $repoQuestion = $em->getRepository("AppBundle:Question");
        $translator = $this->get("translator");
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        $questions = $repoQuestion->findBy(array("city" => null));
        
        return $this->render('default/answer_form.html.twig', [
            "cities"    => $cities,
            "questions" => $questions
        ]);
    }
    
    /**
     * @Route("/repondre2", name="answer_form_new")
     */
    public function answerFormNewAction(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        $translator = $this->get("translator");
        $draftId = $request->get("draft");
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        
        // Get the draft in DB
        $electoralList = null;
        
        if(!empty($draftId)) {
            $electoralList = $repoElectoralList->findOneByDraftId($draftId);
            
            if($electoralList == null) {
                // Error, no draft found
                $this->addFlash(
                    'warning',
                    $translator->trans("unknown_draft")
                );
            }
            elseif($electoralList->getStatus() != ElectoralList::STATUS_DRAFT) {
                // Error, no draft found
                $this->addFlash(
                    'warning',
                    $translator->trans("draft_completed")
                );
            }
        }
        
        if($request->getMethod() == "POST" && ($electoralList == null || $electoralList->getStatus() == ElectoralList::STATUS_DRAFT)) {
            
            if(empty($draftId)) {
                // New draft
                $electoralList = new ElectoralList();
                
                $city = $repoCity->findOneByInsee($request->get("city"));
                $electoralList->setCity($city);
                
                // Set email confirmation code
                $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $confirmationCode = "";
                for ($i = 0; $i < 32; $i++) {
                    $confirmationCode .= $characters[rand(0, strlen($characters) - 1)];
                }
                $electoralList->setConfirmationCode($confirmationCode);
                
                // Set draft id
                $tempDraftId = "";
                do {
                    $tempDraftId = "";
                    for ($i = 0; $i < 16; $i++) {
                        $tempDraftId .= $characters[rand(0, strlen($characters) - 1)];
                    }
                } while($repoElectoralList->findOneByDraftId($tempDraftId) != null);
                
                $electoralList->setDraftId($tempDraftId);
            }
            
            
            // Update fields
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
            
            // Persist if new draft
            if(empty($draftId)) {
                $em->persist($electoralList);
            }
            
            $em->flush();// Save
            
            if(empty($draftId)) {
                // Send email with draft URL
                try {
                    $message = (new \Swift_Message($translator->trans("mail_draft_title")))
                    ->setFrom($this->getParameter("mailer_user"))
                    ->setTo($electoralList->getContactEmail())
                    ->setBody(
                        $this->renderView(
                            'email/draft.html.twig',
                            ['url' => $this->generateUrl('answer_form_questions', array('step' => 1, 'draft' => $electoralList->getDraftId()), UrlGeneratorInterface::ABSOLUTE_URL)]
                            ),
                        'text/html'
                    )
                    ;
                    $mailer->send($message);
                    
                } catch(\Exception $e) {
                    $this->addFlash(
                        'warning',
                        $translator->trans("confirmation_email_not_sent")
                    );
                }
            }
            
            return $this->redirectToRoute('answer_form_questions', array("step" => 1, "draft" => $electoralList->getDraftId()));
        }
        
        return $this->render('default/answer_form_new.html.twig', [
            "cities"        => $cities,
            "electoralList" => $electoralList
        ]);
    }
    
    /**
     * @Route("/repondre/{step}/{draft}", name="answer_form_questions")
     */
    public function answerQuestionsAction(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        $repoQuestion = $em->getRepository("AppBundle:Question");
        $repoQuestionCategory = $em->getRepository("AppBundle:QuestionCategory");
        $translator = $this->get("translator");
        $draftId = $request->get("draft");
        $step = intval($request->get("step"));
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        $category = $repoQuestionCategory->findOneByPosition($step);
        $questions = array();
        $nbSteps = 0;
        $previousStep = null;
        $nextStep = null;
        
        // Get the draft in DB
        $electoralList = null;
        $noError = true;
        
        if($category == null) {
            // Error, the page does not exist
            $this->addFlash(
                'warning',
                $translator->trans("unknown_page")
                );
            
            $noError = false;
        }
        else {
            $electoralList = $repoElectoralList->findOneByDraftId($draftId);
            
            if($electoralList == null) {
                // Error, no draft found
                $this->addFlash(
                    'warning',
                    $translator->trans("unknown_draft")
                    );
                
                $noError = false;
            }
            elseif($electoralList->getStatus() != ElectoralList::STATUS_DRAFT) {
                // Error, no draft found
                $this->addFlash(
                    'warning',
                    $translator->trans("draft_completed")
                    );
                
                $electoralList = null;
                $noError = false;
            }
        }
        
        if($noError) {
            // Get steps
            $categories = $repoQuestionCategory->findBy(array(), array("position" => "ASC"));
            $previousCategory = null;
            foreach($categories as $tempCategory) {
                
                $willBeUsed = false;
                
                foreach($tempCategory->getQuestions() as $question) {
                    
                    if($question->getCity() == null || $question->getCity()->getId() == $electoralList->getCity()->getId()) {
                        $willBeUsed = true;
                    }
                }
                
                if($willBeUsed) {
                    $nbSteps++;
                    
                    if($previousCategory != null && $previousCategory->getPosition() < $category->getPosition()) {
                        $previousStep = $previousCategory->getPosition();
                    }
                    
                    if($nextStep == null && $tempCategory->getPosition() > $category->getPosition()) {
                        $nextStep = $tempCategory->getPosition();
                    }
                    
                    $previousCategory = $tempCategory;
                }
            }
            
            
            // Get questions
            $questions = $repoQuestion->findBy(array("city" => null, "category" => $category->getPosition()));
            foreach($electoralList->getCity()->getQuestions() as $question) {
                
                if($question->getCategory()->getId() == $category->getId()) {
                    $questions[] = $question;
                }
            }
            
            if($request->getMethod() == "POST") {
                
                // Delete previous answers
                foreach($electoralList->getAnswers() as $answer) {
                    $em->remove($answer);
                }
                
                // Create new answers
                foreach($questions as $question) {
                    $answer = new Answer();
                    $answer->setQuestion($question);
                    $answer->setList($electoralList);
                    $answer->setText(trim($request->get("text_".$question->getId())));
                    $answer->setValue(trim($request->get("radio_answerValue_".$question->getId())));
                    $em->persist($answer);
                }
                
                
                $em->flush();
                
                if($nextStep != null) {
                    return $this->redirectToRoute('answer_form_questions', array("step" => $nextStep, "draft" => $electoralList->getDraftId()));
                }
                else {
                    return $this->redirectToRoute('answer_form_overview', array("draft" => $electoralList->getDraftId()));
                }
            }
        }
        
        return $this->render('default/answer_questions.html.twig', [
            "questions"     => $questions,
            "electoralList" => $electoralList,
            "category"      => $category,
            "nbSteps"       => $nbSteps,
            "previousStep"  => $previousStep,
            "nextStep"      => $nextStep
        ]);
    }
    
    /**
     * @Route("/repondre/recapitulatif/{draft}", name="answer_form_overview")
     */
    public function answerOverviewAction(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        $repoQuestion = $em->getRepository("AppBundle:Question");
        $repoQuestionCategory = $em->getRepository("AppBundle:QuestionCategory");
        $translator = $this->get("translator");
        $draftId = $request->get("draft");
        $step = intval($request->get("step"));
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        $category = $repoQuestionCategory->findOneByPosition($step);
        $questions = array();
        $nbSteps = 0;
        $previousStep = null;
        $nextStep = null;
        
        // Get the draft in DB
        $electoralList = null;
        $noError = true;
        
        if($category == null) {
            // Error, the page does not exist
            $this->addFlash(
                'warning',
                $translator->trans("unknown_page")
                );
            
            $noError = false;
        }
        else {
            $electoralList = $repoElectoralList->findOneByDraftId($draftId);
            
            if($electoralList == null) {
                // Error, no draft found
                $this->addFlash(
                    'warning',
                    $translator->trans("unknown_draft")
                    );
                
                $noError = false;
            }
            elseif($electoralList->getStatus() != ElectoralList::STATUS_DRAFT) {
                // Error, no draft found
                $this->addFlash(
                    'warning',
                    $translator->trans("draft_completed")
                    );
                
                $electoralList = null;
                $noError = false;
            }
        }
        
        if($noError) {
            // Get steps
            $categories = $repoQuestionCategory->findBy(array(), array("position" => "ASC"));
            $previousCategory = null;
            foreach($categories as $tempCategory) {
                
                $willBeUsed = false;
                
                foreach($tempCategory->getQuestions() as $question) {
                    
                    if($question->getCity() == null || $question->getCity()->getId() == $electoralList->getCity()->getId()) {
                        $willBeUsed = true;
                    }
                }
                
                if($willBeUsed) {
                    $nbSteps++;
                    
                    if($previousCategory != null && $previousCategory->getPosition() < $category->getPosition()) {
                        $previousStep = $previousCategory->getPosition();
                    }
                    
                    if($nextStep == null && $tempCategory->getPosition() > $category->getPosition()) {
                        $nextStep = $tempCategory->getPosition();
                    }
                    
                    $previousCategory = $tempCategory;
                }
            }
            
            
            // Get questions
            $questions = $repoQuestion->findBy(array("city" => null, "category" => $category->getPosition()));
            foreach($electoralList->getCity()->getQuestions() as $question) {
                
                if($question->getCategory()->getId() == $category->getId()) {
                    $questions[] = $question;
                }
            }
            
            if($request->getMethod() == "POST") {
                
                // Delete previous answers
                foreach($electoralList->getAnswers() as $answer) {
                    $em->remove($answer);
                }
                
                // Create new answers
                foreach($questions as $question) {
                    $answer = new Answer();
                    $answer->setQuestion($question);
                    $answer->setList($electoralList);
                    $answer->setText(trim($request->get("text_".$question->getId())));
                    $answer->setValue(trim($request->get("radio_answerValue_".$question->getId())));
                    $em->persist($answer);
                }
                
                
                $em->flush();
                
                if($nextStep != null) {
                    return $this->redirectToRoute('answer_form_questions', array("step" => $nextStep, "draft" => $electoralList->getDraftId()));
                }
                else {
                    return $this->redirectToRoute('answer_form_overview', array("draft" => $electoralList->getDraftId()));
                }
            }
        }
        
        return $this->render('default/answer_overview.html.twig', [
            "questions"     => $questions,
            "electoralList" => $electoralList,
            "category"      => $category,
            "nbSteps"       => $nbSteps,
            "previousStep"  => $previousStep,
            "nextStep"      => $nextStep
        ]);
    }
    
    /**
     * @Route("/repondre/envoyer", name="post_answer_form")
     */
    public function postAnswerFormAction(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCity = $em->getRepository("AppBundle:City");
        $repoQuestion = $em->getRepository("AppBundle:Question");
        $translator = $this->get("translator");
        
        $params = array(
            "success"   => false,
            "message"   => null
        );
        
        if($request->getMethod() == "POST") {
            
            $questions = $repoQuestion->findBy(array("city" => null));
            
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
            
            // Send email with confirmation code
            $emailError = false;
            try {
                $message = (new \Swift_Message($translator->trans("mail_confirmation_title")))
                ->setFrom($this->getParameter("mailer_user"))
                ->setTo($electoralList->getContactEmail())
                ->setBody(
                    $this->renderView(
                        'email/confirmation.html.twig',
                        ['url' => $this->generateUrl('confirmation', array('list' => $electoralList->getId(), 'code' => $electoralList->getConfirmationCode()), UrlGeneratorInterface::ABSOLUTE_URL)]
                    ),
                    'text/html'
                )
                ;
                $mailer->send($message);
                
            } catch(\Exception $e) {
                $emailError = true;
                $params = array(
                    "success"   => true,
                    "message"   => $translator->trans("confirmation_email_not_sent")
                );
            }
            
            if(!$emailError) {
                $params = array(
                    "success"   => true,
                    "message"   => null
                );
            }
        }
        
        $response = new Response(json_encode($params));
        $response->headers->set("Content-Type", "application/json");
        return $response;
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
     * @Route("/confirmation/{list}/{code}", name="confirmation")
     */
    public function confirmationFormAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        $translator = $this->get("translator");
        
        $electoralList = $repoElectoralList->find($request->get("list"));
        
        if($electoralList == null || $electoralList->getConfirmationCode() != $request->get("code")) {
            $this->addFlash(
                'warning',
                $translator->trans("confirmation_code_does_not_match")
            );
        }
        else {
            $electoralList->setConfirmedByEmail(true);
            $electoralList->setConfirmationDate(new \Datetime());
            $em->flush();
            
            $this->addFlash(
                'success',
                $translator->trans("form_confirmed")
            );
        }
        
        return $this->render('default/confirmation_form.html.twig', [
        ]);
    }
    
    /**
     * @Route("/questions", name="questions")
     */
    public function questionsAction(Request $request)
    {
        return $this->render('default/questions.html.twig', [
        ]);
    }
    
    /**
     * @Route("/posez-une-question", name="ask_question")
     */
    public function askQuestionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoCity = $em->getRepository("AppBundle:City");
        
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        
        if($request->getMethod() == "POST") {
            $originalQuestion = new OriginalQuestion();
            $originalQuestion->setText(trim($request->get("text")));
            
            $tempCities = array();
            
            foreach($cities as $city) {
                if($request->get("city_".$city->getId()) == "".$city->getId()) {
                    $tempCities[] = $city;
                }
            }
            
            if(count($tempCities) < count($cities)) {
                $originalQuestion->setCities($tempCities);
            }
            
            $em->persist($originalQuestion);
            $em->flush();
            
            $this->addFlash(
                'success',
                $translator->trans("ask_question_succeeded")
            );
        }
        
        return $this->render('default/ask_question.html.twig', [
            "cities"    => $cities
        ]);
    }
    
    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request, \Swift_Mailer $mailer)
    {
        $translator = $this->get("translator");
        
        if($request->getMethod() == "POST") {
            
            $message = (new \Swift_Message($translator->trans("contact_form", ["%app%" => $this->getParameter("name")])))
            ->setFrom($this->getParameter("mailer_user"))
            ->setTo($this->getParameter("mailer_user"))
            ->setBody(
                $this->renderView(
                    'email/contact.html.twig',
                    [
                        "body" => $request->get("body"), 
                        "firstname" => $request->get("firstname"),
                        "lastname" => $request->get("lastname"),
                        "email" => $request->get("email")
                    ]
                    ),
                'text/html'
            )
            ;
            
            $mailer->send($message);
            
            $this->addFlash("success", $translator->trans("email_sent"));
        }
        
        return $this->render('default/contact.html.twig', [
        ]);
    }
    
    /**
     * @Route("/mentions-legales", name="terms")
     */
    public function termsAction(Request $request)
    {
        return $this->render('default/terms.html.twig', [
        ]);
    }
}
