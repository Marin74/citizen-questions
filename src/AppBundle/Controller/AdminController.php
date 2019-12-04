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
     * @Route("/admin/list/{list}", name="admin_list")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $em = $this->getDoctrine()->getManager();
        $repoElectoralList = $em->getRepository("AppBundle:ElectoralList");
        
        return $this->render('admin/electoral_list.html.twig', [
            "list"  => $repoElectoralList->find($request->get("list"))
        ]);
    }
}
