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
 * UJM\ExoBundle\Entity\LinkHintPaper
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\LinkHintPaperRepository")
 * @ORM\Table(name="ujm_link_hint_paper")
 */
class LinkHintPaper
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Hint")
     */
    private $hint;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Paper")
     */
    private $paper;

    /**
     * @var boolean $view
     *
     * @ORM\Column(name="view", type="boolean")
     */
    private $view;

    public function __construct(\UJM\ExoBundle\Entity\Hint $hint, \UJM\ExoBundle\Entity\Paper $paper)
    {
        $this->hint = $hint;
        $this->paper = $paper;
    }

    public function setHint(\UJM\ExoBundle\Entity\Hint $hint)
    {
        $this->hint = $hint;
    }

    public function getHint()
    {
        return $this->hint;
    }

    public function setPaper(\UJM\ExoBundle\Entity\Paper $paper)
    {
        $this->paper = $paper;
    }

    public function getPaper()
    {
        return $this->paper;
    }

    /**
     * Set view
     *
     * @param boolean $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Get view
     */
    public function getView()
    {
        return $this->view;
    }
}