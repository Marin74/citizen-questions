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
use AppBundle\Entity\User;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin_homepage")
     */
    public function adminAction(Request $request)
    {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $em = $this->getDoctrine()->getManager();
        $repoOriginalQuestion = $em->getRepository("AppBundle:OriginalQuestion");
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        
        return $this->render('admin/index.html.twig', [
            "lastOriginalQuestions" => $repoOriginalQuestion->findBy(array(), array("id" => "DESC")),
            "listsToValidate"       => $repoElectoralList->findBy(array("status" => ElectoralList::STATUS_PENDING), array("id" => "ASC"))
        ]);
    }
    
    /**
     * @Route("/admin/questions-originales", name="admin_original_questions")
     */
    public function originalQuestionsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $em = $this->getDoctrine()->getManager();
        $repoOriginalQuestion = $em->getRepository("AppBundle:OriginalQuestion");
        
        return $this->render('admin/original_questions.html.twig', [
            "originalQuestions" => $repoOriginalQuestion->findBy(array(), array("id" => "DESC"))
        ]);
    }
    
    /**
     * @Route("/admin/listes", name="admin_electoral_lists")
     */
    public function electoralListsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $em = $this->getDoctrine()->getManager();
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        
        return $this->render('admin/electoral_lists.html.twig', [
            "lists" => $repoElectoralList->findBy(array(), array("id" => "DESC"))
        ]);
    }
    
    /**
     * @Route("/admin/liste/{list}", name="admin_list")
     */
    public function listAction(Request $request, \Swift_Mailer $mailer)
    {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $em = $this->getDoctrine()->getManager();
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        $repoNotificationEmail = $em->getRepository("AppBundle:NotificationEmail");
        $translator = $this->get("translator");
        $action = $request->get("action");
        
        $list = $repoElectoralList->find($request->get("list"));
        
        if($list != null && $request->getMethod() == "POST" && !empty($action)) {
            
            $sendEmails = true;
            
            if($list->getValidationDate() != null) {
                $sendEmails = false;
            }
            
            if($action == ElectoralList::STATUS_PENDING) {
                $list->setStatus(ElectoralList::STATUS_PENDING);
            }
            elseif($action == ElectoralList::STATUS_VALIDATED) {
                $list->setStatus(ElectoralList::STATUS_VALIDATED);
                $list->setValidationDate(new \Datetime);
            }
            if($action == ElectoralList::STATUS_REFUSED) {
                $list->setStatus(ElectoralList::STATUS_REFUSED);
            }
            $em->flush();
            
            if($action == ElectoralList::STATUS_VALIDATED && $sendEmails) {
                
                $notificationEmails = $repoNotificationEmail->findByCity($list->getCity());
                
                foreach($notificationEmails as $notificationEmail) {
                    
                    try {
                        $message = (new \Swift_Message($translator->trans("notification_email_title", ["%city%" => $list->getCity()->getName()])))
                        ->setFrom($this->getParameter("mailer_user"))
                        ->setTo($notificationEmail->getEmail())
                        ->setBody(
                            $this->renderView(
                                'email/notification.html.twig',
                                [
                                    'list' => $list->getName(),
                                    'city' => $list->getCity()->getName(),
                                    'url' => $this->generateUrl('list', array('id' => $list->getId(), 'name' => $list->getNameForUrl()), UrlGeneratorInterface::ABSOLUTE_URL)
                                ]
                            ),
                            'text/html'
                        )
                        ;
                        $mailer->send($message);
                        
                    } catch(\Exception $e) {
                        $this->addFlash(
                            'warning',
                            $translator->trans("notification_email_not_sent", ["%email%" => $notificationEmail->getEmail()])
                        );
                    }
                }
                
                
                // Send a email to the list
                try {
                    $message = (new \Swift_Message($translator->trans("list_notification_email_title", ["%city%" => $list->getCity()->getName()])))
                    ->setFrom($this->getParameter("mailer_user"))
                    ->setTo($list->getContactEmail())
                    ->setBody(
                        $this->renderView(
                            'email/list_notification.html.twig',
                            [
                                'list' => $list->getName(),
                                'city' => $list->getCity()->getName(),
                                'url' => $this->generateUrl('list', array('id' => $list->getId(), 'name' => $list->getNameForUrl()), UrlGeneratorInterface::ABSOLUTE_URL)
                            ]
                        ),
                        'text/html'
                    )
                    ;
                    $mailer->send($message);
                    
                } catch(\Exception $e) {
                    $this->addFlash(
                        'warning',
                        $translator->trans("list_notification_email_not_sent", ["%email%" => $notificationEmail->getEmail()])
                    );
                }
            }
        }
        
        return $this->render('admin/electoral_list.html.twig', [
            "list"  => $list
        ]);
    }
}
