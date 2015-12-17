<?php

namespace Flower\ClientsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Flower\MarketingBundle\Model\EmailGrade;

/**
* ContactRepository
*
* This class was generated by the Doctrine ORM. Add your own custom
* repository methods below.
*/
class ContactRepository extends EntityRepository
{

    public function getByAccountQuery($accountId)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("c");
        $qb->join("c.accounts", "a");
        $qb->where("a.id = :account_id");
        $qb->setParameter("account_id", $accountId);

        return $qb;
    }

    public function getByContactListQuery($contactListId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->from('FlowerModelBundle:Clients\Contact c INNER JOIN FlowerModelBundle:Marketing\ContactList cl');
        $qb->andWhere("cl.id = :contact_list_id");
        $qb->setParameter("contact_list_id", $contactListId);

        return $qb;
    }

    public function getCountByContactList($contactListId)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("COUNT(c)");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl", "WITH", "1=1");
        $qb->join("cl.contacts", "c2");
        $qb->where("cl.id = :contact_list_id");
        $qb->andWhere("c2.id = c.id");
        $qb->setParameter("contact_list_id", $contactListId);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getByContactList($contactListId, $offset, $limit)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl", "WITH", "1=1");
        $qb->join("cl.contacts", "c2");
        $qb->where("cl.id = :contact_list_id");
        $qb->andWhere("c2.id = c.id");
        $qb->setParameter("contact_list_id", $contactListId);

        $qb->setFirstResult($offset * $limit);
        $qb->setMaxResults($limit);


        return $qb->getQuery()->getResult();
    }

    public function getDistinctEmailsByContactsLists($contactsLists, $offset, $limit)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("DISTINCT c.email, c.id");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl", "WITH", "1=1");
        $qb->join("cl.contacts", "c2");
        $qb->where("cl.id IN (:contact_list_ids)");
        $qb->andWhere("c2.id = c.id");
        $qb->andWhere("c.allowCampaignMail = :allowCampaignMail")->setParameter("allowCampaignMail", true);
        $qb->andWhere("c.emailGrade != :emailgrade_can_be_send")->setParameter("emailgrade_can_be_send", EmailGrade::grade_d);
        $qb->setParameter("contact_list_ids", $contactsLists);

        $qb->setFirstResult($offset * $limit);
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get contacts by contact lists.
     * @param  array $contactsLists [description]
     * @param  int $offset        [description]
     * @param  int $limit         [description]
     * @return [type]                [description]
     */
    public function getByContactsLists($contactsLists, $offset, $limit)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl");
        $qb->where("cl.id IN (:contact_list_id)");
        $qb->setParameter("contact_list_id", $contactsLists);

        $qb->setFirstResult($offset * $limit);
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /*
    Includin duplicates
    */
    public function getCountEmailsByContactsLists($contactsLists)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("COUNT(c.email)");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl");
        $qb->where("cl.id IN (:contacts_lists)");
        $qb->andWhere("c.email != ''");
        $qb->setParameter("contacts_lists", $contactsLists);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }

    /*
    Includin duplicates
    */

    public function getPageCountEmailsByContactsLists($contactsLists, $pageSize)
    {
        $count = $this->getCountEmailsByContactsLists($contactsLists);
        return ceil($count / $pageSize);
    }

    /*
    Includin duplicates
    */

    public function getEmailsByContactsLists($contactsLists, $offset, $limit)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("c");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl");
        $qb->where("cl.id IN (:contact_list_id)");
        $qb->andWhere("c.email != ''");
        $qb->setParameter("contact_list_id", $contactsLists);

        $qb->setFirstResult($offset * $limit);
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     *Excludin duplicates
     */
    public function getCountByContactsLists($contactsLists)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("COUNT(DISTINCT c.email)");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl", "WITH", "1=1");
        $qb->join("cl.contacts", "c2");
        $qb->where("cl.id IN (:contact_list_ids)");
        $qb->andWhere("c2.id = c.id");
        $qb->andWhere("c.allowCampaignMail = :allowCampaignMail")->setParameter("allowCampaignMail", true);
        $qb->andWhere("c.emailGrade != :emailgrade_can_be_send")->setParameter("emailgrade_can_be_send", EmailGrade::grade_d);
        $qb->setParameter("contact_list_ids", $contactsLists);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }

    /**
     * Count all by contactlists.
     */
    public function getCountAllByContactsLists($contactsLists)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("COUNT(distinct c.email)");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl");
        $qb->where("cl.id IN (:contacts_lists)");
        $qb->setParameter("contacts_lists", $contactsLists);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }

    /**
     * Excludin duplicates
     */
    public function getPageCountByContactsLists($contactsLists, $pageSize)
    {
        $count = $this->getCountByContactsLists($contactsLists);
        return ceil($count / $pageSize);
    }

    /**
     * Excludin duplicates
     */
    public function getDistinctPageCountByContactsLists($contactsLists, $pageSize)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->select("COUNT(distinct c.email)");
        $qb->join("FlowerModelBundle:Marketing\ContactList", "cl", "WITH", "1=1");
        $qb->join("cl.contacts", "c2");
        $qb->where("cl.id IN (:contact_list_ids)");
        $qb->andWhere("c2.id = c.id");
        $qb->andWhere("c.allowCampaignMail = :allowCampaignMail")->setParameter("allowCampaignMail", true);
        $qb->andWhere("c.emailGrade != :emailgrade_can_be_send")->setParameter("emailgrade_can_be_send", EmailGrade::grade_d);
        $qb->setParameter("contact_list_ids", $contactsLists);

        $count = $qb->getQuery()->getSingleScalarResult();
        return ceil($count / $pageSize);
    }

    /**
     * Excludin duplicates
     */
    public function getPageCountAllByContactsLists($contactsLists, $pageSize)
    {
        $count = $this->getCountAllByContactsLists($contactsLists);
        return ceil($count / $pageSize);
    }

    public function search($completeText, $texts, $limit = 10)
    {
        $qb = $this->createQueryBuilder("c");
        $qb->orWhere("c.lastname like :text")
            ->orWhere("c.firstname like :text")
            ->orWhere("c.phone like :text")
            ->orWhere("c.email like :text")
            ->setParameter("text", "%".$completeText."%");
        $qb->setMaxResults($limit);

        $result = $qb->getQuery()->getResult();
        $qb = $this->createQueryBuilder("c");
        $count = 0;
        foreach ($texts as $text) {
            $qb->orWhere("c.lastname like :text_".$count)
            ->orWhere("c.firstname like :text_".$count)
            ->setParameter("text_".$count, "%".$text."%");
            $qb->setMaxResults($limit);
            $count ++;
        }
        $result = array_merge($result,$qb->getQuery()->getResult());
        return array_unique($result, SORT_REGULAR);

    }

}
