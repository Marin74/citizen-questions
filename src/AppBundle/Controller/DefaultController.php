<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\Answer;
use AppBundle\Entity\ElectoralList;
use AppBundle\Entity\NotificationEmail;
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
        $repoAnswer = $em->getRepository("AppBundle:Answer");
        $repoCity = $em->getRepository("AppBundle:City");
        $repoQuestion = $em->getRepository("AppBundle:Question");
        $repoNotificationEmail = $em->getRepository("AppBundle:NotificationEmail");
        $translator = $this->get("translator");
        
        $city = $repoCity->findOneByInsee($request->get("insee"));
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        $email = trim($request->get("email"));
        $questions = array();
        $answers = array();
        
        if($city != null) {
            
            $qb = $repoAnswer->createQueryBuilder("a");
            $qb->innerJoin("a.question", "q")
            ->innerJoin("q.category", "c")
            ->innerJoin("a.list", "l")
            ->where($qb->expr()->in("a.list", ":lists"))
            ->andWhere($qb->expr()->eq("l.status", ":status"))
            ->setParameter("lists", $city->getLists())
            ->setParameter("status", ElectoralList::STATUS_VALIDATED)
            ->orderBy("c.position", "ASC")
            ->addOrderBy("q.id", "ASC")
            ->addOrderBy("l.id", "ASC")
            ;
            
            $answers = $qb->getQuery()->execute();
            
            if(count($answers) == 0) {
                
                $generalQuestions = $repoQuestion->findBy(array("city" => null, "groupOfCities" => null));
                
                $dictQuestions = array();// "POSITION-CATEGORY-QUESTION" => question
                foreach($generalQuestions as $question) {
                    $index = "";
                    if($question->getId() >= 100) {
                        $index = "".$question->getId();
                    }
                    elseif($question->getId() >= 10) {
                        $index = "0".$question->getId();
                    }
                    else {// < 10
                        $index = "00".$question->getId();
                    }
                    
                    $dictQuestions[$question->getCategory()->getPosition()."-".$question->getCategory()->getId()."-".$index] = $question;
                }
                foreach($city->getQuestions() as $question) {
                    $index = "";
                    if($question->getId() >= 100) {
                        $index = "".$question->getId();
                    }
                    elseif($question->getId() >= 10) {
                        $index = "0".$question->getId();
                    }
                    else {// < 10
                        $index = "00".$question->getId();
                    }
                    
                    $dictQuestions[$question->getCategory()->getPosition()."-".$question->getCategory()->getId()."-".$index] = $question;
                }
                foreach($city->getGroupsOfCities() as $groupOfCities) {
                    
                    foreach($groupOfCities->getQuestions() as $question) {
                        $index = "";
                        if($question->getId() >= 100) {
                            $index = "".$question->getId();
                        }
                        elseif($question->getId() >= 10) {
                            $index = "0".$question->getId();
                        }
                        else {// < 10
                            $index = "00".$question->getId();
                        }
                        
                        $dictQuestions[$question->getCategory()->getPosition()."-".$question->getCategory()->getId()."-".$index] = $question;
                    }
                }
                
                ksort($dictQuestions);// Sort by key
                
                foreach($dictQuestions as $question) {
                    $questions[] = $question;
                }
            }
        }
        
        
        if($request->getMethod() == "POST" && $city != null && !empty($email)) {
            
            // Check if the email already exists
            $notificationEmail = $repoNotificationEmail->findOneBy(array("email" => $email, "city" => $city));
            
            if($notificationEmail == null) {
                
                $notificationEmail = new NotificationEmail();
                $notificationEmail->setEmail($email);
                $notificationEmail->setCity($city);
                $em->persist($notificationEmail);
                $em->flush();
            }
            
            $this->addFlash(
                'success',
                $translator->trans("notification_email_added", ["%city%" => $city->getName()])
            );
        }
        
        return $this->render('default/city.html.twig', [
            "ogTitle"   => $city == null ? null : $city->getName(),
            "city"      => $city,
            "cities"    => $cities,
            "answers"   => $answers,
            "questions" => $questions
        ]);
    }
    
    /**
     * @Route("/liste/{id}/{name}", name="list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnswer = $em->getRepository("AppBundle:Answer");
        $repoCity = $em->getRepository("AppBundle:City");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        
        $list = $repoElectoralList->findOneBy(array("id" => $request->get("id"), "status" => ElectoralList::STATUS_VALIDATED));
        $cities = $repoCity->findBy(array(), array("name" => "ASC"));
        
        $answers = array();
        
        if($list != null) {
            $qb = $repoAnswer->createQueryBuilder("a");
            $qb->innerJoin("a.question", "q")
            ->innerJoin("q.category", "c")
            ->where($qb->expr()->eq("a.list", ":list"))
            ->setParameter("list", $list)
            ->orderBy("c.position", "ASC")
            ->addOrderBy("q.id", "ASC")
            ;
            
            $answers = $qb->getQuery()->execute();
        }
        
        // replace this example code with whatever you need
        return $this->render('default/list.html.twig', [
            "ogTitle"   => $list == null ? null : $list->getName(),
            "list"      => $list,
            "cities"    => $cities,
            "answers"   => $answers
        ]);
    }
    
    /**
     * @Route("/repondre", name="answer_form")
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
                
                $electoralList = null;
            }
        }
        
        if($request->getMethod() == "POST" && ($electoralList == null || $electoralList->getStatus() == ElectoralList::STATUS_DRAFT)) {
            
            if(empty($draftId)) {
                // New draft
                $electoralList = new ElectoralList();
                
                $city = $repoCity->findOneByInsee($request->get("city"));
                $electoralList->setCity($city);
                
                // Set draft id
                $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
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
        
        return $this->render('default/answer_form.html.twig', [
            "cities"        => $cities,
            "electoralList" => $electoralList
        ]);
    }
    
    /**
     * @Route("/repondre/{step}/{draft}", name="answer_form_questions", requirements={"step"="\d+"})
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
        $usedCategories = array();
        $usedCategoriesFull = array();
        
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
            
            $questionsAnsweredIds = array();
            foreach($electoralList->getAnswers() as $answer) {
                
                $questionsAnsweredIds[] = $answer->getQuestion()->getId();
            }
            
            foreach($categories as $tempCategory) {
                
                $willBeUsed = false;
                $isFullyCompleted = true;
                
                foreach($tempCategory->getQuestions() as $question) {
                    
                    if($question->concernsThisCity($electoralList->getCity())) {
                        $willBeUsed = true;
                        
                        // Check if the category is fully completed
                        if(!in_array($question->getId(), $questionsAnsweredIds)) {
                            $isFullyCompleted = false;
                        }
                    }
                }
                
                if($willBeUsed) {
                    $nbSteps++;
                    $usedCategories[] = $tempCategory;
                    $usedCategoriesFull[] = $isFullyCompleted;
                    
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
            $questions = array();
            $tempQuestions = $repoQuestion->findAll();
            foreach($tempQuestions as $question) {
                
                if($question->concernsThisCity($electoralList->getCity()) 
                    && $question->getCategory()->getId() == $category->getId()
                ) {
                    $questions[] = $question;
                }
            }
            
            if($request->getMethod() == "POST") {
                
                // Delete previous answers
                foreach($electoralList->getAnswers() as $answer) {
                    if($answer->getQuestion()->getCategory()->getId() == $category->getId()) {
                        $em->remove($answer);
                    }
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
            "questions"             => $questions,
            "electoralList"         => $electoralList,
            "category"              => $category,
            "nbSteps"               => $nbSteps,
            "previousStep"          => $previousStep,
            "nextStep"              => $nextStep,
            "usedCategories"        => $usedCategories,
            "usedCategoriesFull"    => $usedCategoriesFull
        ]);
    }
    
    /**
     * @Route("/repondre/recapitulatif/{draft}", name="answer_form_overview")
     */
    public function answerOverviewAction(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnswer = $em->getRepository("AppBundle:Answer");
        $repoCity = $em->getRepository("AppBundle:City");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        $repoQuestion = $em->getRepository("AppBundle:Question");
        $repoQuestionCategory = $em->getRepository("AppBundle:QuestionCategory");
        $translator = $this->get("translator");
        $draftId = $request->get("draft");
        $nbSteps = 0;
        $usedCategories = array();
        $usedCategoriesFull = array();
        $answers = array();
        
        // Get the draft in DB
        $list = $repoElectoralList->findOneByDraftId($draftId);
        
        if($list == null) {
            // Error, no draft found
            $this->addFlash(
                'warning',
                $translator->trans("unknown_draft")
            );
        }
        elseif($list->getStatus() != ElectoralList::STATUS_DRAFT) {
            // Error, no draft found
            $this->addFlash(
                'warning',
                $translator->trans("draft_completed")
            );
            
            $list = null;
        }
        else {
            
            // Get steps
            $categories = $repoQuestionCategory->findBy(array(), array("position" => "ASC"));
            $previousCategory = null;
            
            $questionsAnsweredIds = array();
            foreach($list->getAnswers() as $answer) {
                
                $questionsAnsweredIds[] = $answer->getQuestion()->getId();
            }
            
            foreach($categories as $tempCategory) {
                
                $willBeUsed = false;
                $isFullyCompleted = true;
                
                foreach($tempCategory->getQuestions() as $question) {
                    
                    if($question->concernsThisCity($list->getCity())) {
                        $willBeUsed = true;
                        
                        // Check if the category is fully completed
                        if(!in_array($question->getId(), $questionsAnsweredIds)) {
                            $isFullyCompleted = false;
                        }
                    }
                }
                
                if($willBeUsed) {
                    $nbSteps++;
                    $usedCategories[] = $tempCategory;
                    $usedCategoriesFull[] = $isFullyCompleted;
                }
            }
            
            if($request->getMethod() == "POST") {
                
                $list->setStatus(ElectoralList::STATUS_PENDING);
                $em->flush();
                
                // Send email
                $emailError = false;
                try {
                    $message = (new \Swift_Message($translator->trans("mail_confirmation_title")))
                    ->setFrom($this->getParameter("mailer_user"))
                    ->setTo($list->getContactEmail())
                    ->setBody(
                        $this->renderView(
                            'email/confirmation.html.twig',
                            []
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
                
                return $this->redirectToRoute('answer_form_end');
            }
            
            $qb = $repoAnswer->createQueryBuilder("a");
            $qb->innerJoin("a.question", "q")
            ->innerJoin("q.category", "c")
            ->where($qb->expr()->eq("a.list", ":list"))
            ->setParameter("list", $list)
            ->orderBy("c.position", "ASC")
            ->addOrderBy("q.id", "ASC")
            ;
            
            $answers = $qb->getQuery()->execute();
        }
        
        return $this->render('default/answer_overview.html.twig', [
            "electoralList"         => $list,
            "nbSteps"               => $nbSteps,
            "usedCategories"        => $usedCategories,
            "usedCategoriesFull"    => $usedCategoriesFull,
            "answers"               => $answers
        ]);
    }
    
    /**
     * @Route("/repondre/fin", name="answer_form_end")
     */
    public function answerFormEndAction(Request $request)
    {
        return $this->render('default/answer_form_end.html.twig', [
        ]);
    }
    
    /**
     * @Route("/questions", name="questions")
     */
    public function questionsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoOriginalQuestion = $em->getRepository("AppBundle:OriginalQuestion");
        
        return $this->render('default/questions.html.twig', [
            "questions" => $repoOriginalQuestion->findBy(array(), array("id" => "ASC"))
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
