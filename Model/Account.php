<?php

namespace Flower\ClientsBundle\Model;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use Flower\ModelBundle\Entity\User\User;

/**
 * Account
 *
 */
class Account
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"search", "public_api"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"search", "public_api"})
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Groups({"search", "public_api"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     * @Groups({"search", "public_api"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="cuit", type="string", length=255, nullable=true)
     * @Groups({"search", "public_api"})
     */
    private $cuit;

    /**
     * @ManyToMany(targetEntity="\Flower\ModelBundle\Entity\Clients\Contact", mappedBy="accounts")
     */
    private $contacts;

    /**
     * @ManyToOne(targetEntity="\Flower\ModelBundle\Entity\User\User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     * */
    private $assignee;

    /**
     * @OneToMany(targetEntity="Board", mappedBy="account")
     * */
    private $boards;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;
    /**
     * @OneToMany(targetEntity="\Flower\ModelBundle\Entity\Clients\Note", mappedBy="account")
     * */
    private $notes;

    /**
     * @ManyToMany(targetEntity="\Flower\ModelBundle\Entity\Board")
     * @JoinTable(name="accounts_boards",
     *      joinColumns={@JoinColumn(name="account_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="board_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $boards;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->boards = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Account
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Account
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     * @return Account
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
     * @return Account
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }


    public function __toString()
    {
        return $this->name;
    }


    /**
     * Set assignee
     *
     * @param \Flower\ModelBundle\Entity\User\User $assignee
     * @return Account
     */
    public function setAssignee(\Flower\ModelBundle\Entity\User\User $assignee = null)
    {
        $this->assignee = $assignee;

        return $this;
    }

    /**
     * Get assignee
     *
     * @return \Flower\ModelBundle\Entity\User\User
     */
    public function getAssignee()
    {
        return $this->assignee;
    }

    /**
     * Set cuit
     *
     * @param string $cuit
     * @return Account
     */
    public function setCuit($cuit)
    {
        $this->cuit = $cuit;

        return $this;
    }

    /**
     * Get cuit
     *
     * @return string
     */
    public function getCuit()
    {
        return $this->cuit;
    }

    /**
     * Add notes
     *
     * @param \Flower\ModelBundle\Entity\Clients\Note $notes
     * @return Account
     */
    public function addNote(\Flower\ModelBundle\Entity\Clients\Note $notes)
    {
        $this->notes[] = $notes;

        return $this;
    }

    /**
     * Remove notes
     *
     * @param \Flower\ModelBundle\Entity\Clients\Note $notes
     */
    public function removeNote(\Flower\ModelBundle\Entity\Clients\Note $notes)
    {
        $this->notes->removeElement($notes);
    }

    /**
     * Get notes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add contacts
     *
     * @param \Flower\ModelBundle\Entity\Clients\Contact $contacts
     * @return Account
     */
    public function addContact(\Flower\ModelBundle\Entity\Clients\Contact $contacts)
    {
        $this->contacts[] = $contacts;

        return $this;
    }

    /**
     * Remove contacts
     *
     * @param \Flower\ModelBundle\Entity\Clients\Contact $contacts
     */
    public function removeContact(\Flower\ModelBundle\Entity\Clients\Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }


    /**
     * Add boards
     *
     * @param \Flower\ModelBundle\Entity\Board $boards
     * @return Account
     */
    public function addBoard(\Flower\ModelBundle\Entity\Board $boards)
    {
        $this->boards[] = $boards;

        return $this;
    }

    /**
     * Remove boards
     *
     * @param \Flower\ModelBundle\Entity\Board $boards
     */
    public function removeBoard(\Flower\ModelBundle\Entity\Board $boards)
    {
        $this->boards->removeElement($boards);
    }

    /**
     * Get boards
     *
     * @return Collection
     */
    public function getBoards()
    {
        return $this->boards;
    }

}
