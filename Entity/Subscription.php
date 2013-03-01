<?php

/**
 * ExoOnLine
 * Copyright or © or Copr. Université Jean Monnet (France), 2012
 * dsi.dev@univ-st-etienne.fr
 *
 * This software is a computer program whose purpose is to [describe
 * functionalities and technical features of your software].
 *
 * This software is governed by the CeCILL license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
*/

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Subscription
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\SubscriptionRepository")
 * @ORM\Table(name="ujm_subscription")
 */
class Subscription
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Exercise")
     */
    private $exercise;

    /**
     * @var boolean $creator
     *
     * @ORM\Column(name="creator", type="boolean")
     */
    private $creator;

    /**
     * @var boolean $admin
     *
     * @ORM\Column(name="admin", type="boolean")
     */
    private $admin;

    public function __construct(\Claroline\CoreBundle\Entity\User $user, \UJM\ExoBundle\Entity\Exercise $exercise)
    {
        $this->user = $user;
        $this->exercise = $exercise;
    }

    public function setuser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    public function getuser()
    {
        return $this->user;
    }

    public function setexercise(\UJM\ExoBundle\Entity\Exercise $exercise)
    {
        $this->produit = $exercise;
    }

    public function getexercise()
    {
        return $this->exercise;
    }


    /**
     * Set creator
     *
     * @param boolean $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Get creator
     *
     * @return boolean
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set admin
     *
     * @param boolean $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * Get admin
     *
     * @return boolean
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}