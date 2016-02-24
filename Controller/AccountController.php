<?php

namespace Flower\ClientsBundle\Controller;

use Flower\ModelBundle\Entity\Board\History;
use PHPExcel;
use PHPExcel_IOFactory;

use Doctrine\ORM\QueryBuilder;
use Flower\ModelBundle\Entity\Clients\Account;
use Flower\ModelBundle\Entity\Board\TaskStatus;
use Flower\ModelBundle\Entity\Board\Board;
use Flower\ModelBundle\Entity\Board\TaskType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Account controller.
 *
 * @Route("/account")
 */
class AccountController extends BaseController
{

    /**
     * Lists all Account entities.
     *
     * @Route("/", name="account")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $accountAlias = "a";
        $qb = $em->getRepository('FlowerModelBundle:Clients\Account')->createQueryBuilder($accountAlias);
        $qb->leftJoin("a.activity", "ac");
        $qb->leftJoin("a.assignee", "u");

        /* filter by org security groups */
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $secGroupSrv = $this->get('user.service.securitygroup');
            $qb = $secGroupSrv->addSecurityGroupFilter($qb, $this->getUser(), $accountAlias);
        }

        $filters = array('activityFilter' => "ac.id", 'accountAssigneeFilter' => "u.id",);

        if ($request->query->has('reset')) {
            $request->getSession()->set('filter.account', null);
            $request->getSession()->set('sort.account', null);
            return $this->redirectToRoute("account");
        }

        $this->saveFilters($request, $filters, 'account', 'account');
        $paginator = $this->filter($qb, 'account', $request);
        $activities = $em->getRepository('FlowerModelBundle:Clients\Activity')->findAll();
        $activityFilter = $request->query->get("activityFilter");
        $users = $em->getRepository('FlowerModelBundle:User\User')->findBy(array(), array("username" => "ASC"));
        $filters = $this->getFilters('account');
        if (!$activityFilter && $filters['activityFilter'] && $filters['activityFilter']["value"]) {
            $activityFilter = $filters['activityFilter']["value"];
        }
        $filters = $this->getFilters('account');
        return array(
            'paginator' => $paginator,
            'accountAssigneeFilter' => isset($filters['accountAssigneeFilter']) ? $filters['accountAssigneeFilter']["value"] : null,
            'users' => $users,
            'activityFilter' => $activityFilter,
            'activities' => $activities,
        );
    }

    /**
     *
     * @Route("/export", name="account_export")
     * @Method("GET")
     */
    public function exportViewAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $accountAlias = 'a';
        $qb = $em->getRepository('FlowerModelBundle:Clients\Account')->createQueryBuilder($accountAlias);
        $qb->leftJoin("a.activity", "ac");
        /* filter by org security groups */
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $secGroupSrv = $this->get('user.service.securitygroup');
            $qb = $secGroupSrv->addSecurityGroupFilter($qb, $this->getUser(), $accountAlias);
        }
        $limit = 20;
        $currPage = $request->query->get('page');
        if ($currPage) {
            $accounts = $this->filter($qb, 'account', $request, $limit, $currPage);
        } else {
            $accounts = $this->filter($qb, 'account', $request, -1);
        }

        $data = $this->get("client.service.account")->accountDataExport($accounts);
        $this->get("client.service.excelexport")->exportData($data, "Cuentas", "Mi descripcion");
        die();
        return $this->redirectToRoute("account");
    }

    /**
     * Finds and displays a Account entity.
     *
     * @Route("/{id}/show", name="account_show", requirements={"id"="\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction(Account $account, Request $request)
    {
        $user = $this->getUser();
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $canSee = $this->get("user.service.securitygroup")->userCanSeeEntity($user, $account);
            if (!$canSee) {
                throw new AccessDeniedException();
            }
        }
        $deleteForm = $this->createDeleteForm($account->getId(), 'account_delete');

        $em = $this->getDoctrine()->getManager();

        $todoStatus = $em->getRepository("FlowerModelBundle:Board\TaskStatus")->findOneBy(array("name" => TaskStatus::STATUS_TODO));
        $accountBoards = $account->getBoards();

        $currentProjects = $em->getRepository("FlowerModelBundle:Project\Project")->findBy(array("account" => $account));

        $qb = $em->getRepository('FlowerModelBundle:Clients\Contact')->getByAccountQuery($account->getId());
        $contacts = $this->get('knp_paginator')->paginate($qb, $request->query->get('page', 1), 50);

        $qb = $em->getRepository('FlowerModelBundle:Clients\CallEvent')->getByAccountQuery($account->getId());
        $accauntcalls = $this->get('knp_paginator')->paginate($qb, $request->query->get('page', 1), 5);

        $editForm = $this->createForm($this->get("form.type.account"), $account, array(
            'action' => $this->generateUrl('account_update', array('id' => $account->getid())),
            'method' => 'PUT',
        ));
        $qb = $em->getRepository('FlowerModelBundle:Clients\Subsidiary')->getByAccountQuery($account->getId());
        $subsidiaries = $this->get('knp_paginator')->paginate($qb, $request->query->get('page', 1), 5);
        return array(
            'edit_form' => $editForm->createView(),
            'accauntcalls' => $accauntcalls,
            'account' => $account,
            'subsidiaries' => $subsidiaries,
            'accountBoards' => $accountBoards,
            'currentProjects' => $currentProjects,
            'contacts' => $contacts,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Account entity.
     *
     * @Route("/new", name="account_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $account = new Account();
        //$account->setAssignee($this->getUser());
        $form = $this->createForm($this->get("form.type.account"), $account);

        return array(
            'account' => $account,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new Account entity.
     *
     * @Route("/create", name="account_create")
     * @Method("POST")
     * @Template("FlowerClientsBundle:Account:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $account = new Account();
        $form = $this->createForm($this->get("form.type.account"), $account);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /* add default security groups */
            $account = $this->get("client.service.account")->addSecurityGroups($account);

            $em->persist($account);
            $em->flush();

            $this->get('board.service.history')->addSimpleUserActivity(History::TYPE_ACCOUNT, $this->getUser(), $account, History::CRUD_CREATE);

            return $this->redirect($this->generateUrl('account_show', array('id' => $account->getId())));
        }

        return array(
            'account' => $account,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Account entity.
     *
     * @Route("/{id}/edit", name="account_edit", requirements={"id"="\d+"})
     * @Method("GET")
     * @Template()
     */
    public function editAction(Account $account)
    {
        $editForm = $this->createForm($this->get("form.type.account"), $account, array(
            'action' => $this->generateUrl('account_update', array('id' => $account->getid())),
            'method' => 'PUT',
        ));
        $deleteForm = $this->createDeleteForm($account->getId(), 'account_delete');

        return array(
            'account' => $account,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Account entity.
     *
     * @Route("/{id}/update", name="account_update", requirements={"id"="\d+"})
     * @Method("PUT")
     * @Template("FlowerClientsBundle:Account:edit.html.twig")
     */
    public function updateAction(Account $account, Request $request)
    {
        $editForm = $this->createForm($this->get("form.type.account"), $account, array(
            'action' => $this->generateUrl('account_update', array('id' => $account->getid())),
            'method' => 'PUT',
        ));
        if ($editForm->handleRequest($request)->isValid()) {

            /* remove previous security groups */
            foreach ($account->getSecurityGroups() as $securityGroup) {
                $account->removeSecurityGroup($securityGroup);
            }

            /* add default security groups */
            $account = $this->get("client.service.account")->addSecurityGroups($account);

            $this->getDoctrine()->getManager()->flush();

            $this->get('board.service.history')->addSimpleUserActivity(History::TYPE_ACCOUNT, $this->getUser(), $account, History::CRUD_UPDATE);

            return $this->redirect($this->generateUrl('account_show', array('id' => $account->getId())));
        }
        $deleteForm = $this->createDeleteForm($account->getId(), 'account_delete');

        return array(
            'account' => $account,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Save order.
     *
     * @Route("/order/{field}/{type}", name="account_sort")
     */
    public function sortAction($field, $type)
    {
        $this->setOrder('account', $field, $type);

        return $this->redirect($this->generateUrl('account'));
    }

    /**
     * @param string $name session name
     * @param string $field field name
     * @param string $type sort type ("ASC"/"DESC")
     */
    protected function setOrder($name, $field, $type = 'ASC')
    {
        $this->getRequest()->getSession()->set('sort.' . $name, array('field' => $field, 'type' => $type));
    }

    /**
     * @param  string $name
     * @return array
     */
    protected function getOrder($name)
    {
        $session = $this->getRequest()->getSession();

        return $session->has('sort.' . $name) ? $session->get('sort.' . $name) : null;
    }

    /**
     * Deletes a Account entity.
     *
     * @Route("/{id}/delete", name="account_delete", requirements={"id"="\d+"})
     * @Method("DELETE")
     */
    public function deleteAction(Account $account, Request $request)
    {
        $form = $this->createDeleteForm($account->getId(), 'account_delete');
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($account);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('account'));
    }

    /**
     * Create Delete form
     *
     * @param integer $id
     * @param string $route
     * @return Form
     */
    protected function createDeleteForm($id, $route)
    {
        return $this->createFormBuilder(null, array('attr' => array('id' => 'delete')))
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Displays a form to create a new Board entity.
     *
     * @Route("/account/{id}/new", name="board_new_to_account")
     * @Method("GET")
     * @Template("FlowerBoardBundle:Board:new.html.twig")
     */
    public function newBoardAction(Account $account)
    {
        $board = new Board();
        $form = $this->createForm($this->get('form.type.board'), $board);
        $form = $this->createForm($this->get("form.type.board"), $board, array(
            'action' => $this->generateUrl('account_board_create', array("id" => $account->getId())),
            'method' => 'POST',
        ));

        return array(
            'board' => $board,
            'form' => $form->createView(),
        );
    }


    /**
     * Creates a new Board entity.
     *
     * @Route("/{id}/create", name="account_board_create")
     * @Method("POST")
     * @Template("FlowerBoardBundle:Board:new.html.twig")
     */
    public function createBoardAction(Account $account, Request $request)
    {
        $board = new Board();
        $form = $this->createForm($this->get('form.type.board'), $board);
        if ($form->handleRequest($request)->isValid()) {
            $account->addBoard($board);
            $em = $this->getDoctrine()->getManager();
            $em->persist($board);
            $em->flush();

            return $this->redirect($this->generateUrl('board_show', array('id' => $board->getId())));
        }

        return array(
            'board' => $board,
            'form' => $form->createView(),
        );
    }
}
