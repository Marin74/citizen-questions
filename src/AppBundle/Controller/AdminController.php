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
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $em = $this->getDoctrine()->getManager();
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        $action = $request->get("action");
        
        $list = $repoElectoralList->find($request->get("list"));
        
        if($list != null && $request->getMethod() == "POST" && !empty($action)) {
            
            if($action == "P") {
                $list->setStatus(ElectoralList::STATUS_PENDING);
                $list->setValidationDate(null);
            }
            elseif($action == "V") {
                $list->setStatus(ElectoralList::STATUS_VALIDATED);
                $list->setValidationDate(new \Datetime);
            }
            if($action == "R") {
                $list->setStatus(ElectoralList::STATUS_REFUSED);
                $list->setValidationDate(null);
            }
            $em->flush();
        }
        
        return $this->render('admin/electoral_list.html.twig', [
            "list"  => $list
        ]);
    }
}
