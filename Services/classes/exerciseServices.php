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

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Request;

class exerciseServices
{
    protected $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine  = $doctrine;
    }

    public function getIP()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function responseQCM($request, $paperID = 0)
    {
        $res = array();
        $interactionQCMID = $request->request->get('interactionQCMToValidated');
        $response = array();

        $em = $this->doctrine->getEntityManager();
        $interQCM = $em->getRepository('UJMExoBundle:InteractionQCM')->find($interactionQCMID);

        if ($interQCM->getTypeQCM()->getId() == 2) {
            $response[] = $request->request->get('choice');
        } else {
            if ($request->request->get('choice') != null) {
                $response = $request->request->get('choice');
            }
        }

        $allChoices = $interQCM->getChoices();

        $penalty = 0;

        $session = $request->getSession();

        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {
                    $penalty += $penal;
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenalty($interQCM->getInteraction(), $paperID);

        }

        $score = $this->qcmMark($interQCM, $response, $allChoices, $penalty);

        $responseID = '';
        foreach ($response as $res) {
            $responseID .= $res.';';
        }

        $res = array(
            'score'    => $score,
            'penalty'  => $penalty,
            'interQCM' => $interQCM,
            'response' => $responseID
        );

        return $res;

    }

    public function qcmMark(\UJM\ExoBundle\Entity\InteractionQCM $interQCM, array $response, $allChoices, $penality)
    {
        $score = 0;

        $rightChoices = array();
        $markByChoice = array();

        if (!$interQCM->getWeightResponse()) {
            foreach ($allChoices as $choice) {
                if ($choice->getRightResponse()) {
                    $rightChoices[] = (string) $choice->getId();
                }
            }

            $result = array_diff($response, $rightChoices);
            $resultBis = array_diff($rightChoices, $response);

            if ( (count($result) == 0) && (count($resultBis) == 0) ) {
                $score = $interQCM->getScoreRightResponse() - $penality;
            } else {
                $score = $interQCM->getScoreFalseResponse() - $penality;
            }
            if ($score < 0) {
                $score = 0;
            }
            $score .= '/'.$interQCM->getScoreRightResponse();
        } else {
            //points par réponse
            $scoreMax = 0;

            foreach ($allChoices as $choice) {
                $markByChoice[(string) $choice->getId()] = $choice->getWeight();
                if ($choice->getRightResponse()) {
                    $scoreMax += $choice->getWeight();
                }
            }

            foreach ($response as $res) {
                $score += $markByChoice[$res];
            }

            if ($score > $scoreMax) {
                $score = $scoreMax;
            }

            $score -= $penality;

            if ($score < 0) {
                $score = 0;
            }
            $score .= '/'.$scoreMax;
        }

        return $score;
    }

    /**
     * Return the number of papers for an exercise and foran user
     *
     */
    public function getNbPaper($uid, $exoID)
    {
        $papers = $this->doctrine
                       ->getEntityManager()
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($uid, $exoID);

        return count($papers);
    }

    private function getPenalty($interaction, $paperID)
    {
        $penalty = 0;
        $em = $this->doctrine->getEntityManager();

        $hints = $interaction->getHints();

        foreach ($hints as $hint) {
            $lhp = $this->doctrine
                        ->getEntityManager()
                        ->getRepository('UJMExoBundle:LinkHintPaper')
                        ->getLHP($hint->getId(), $paperID);
            if (count($lhp) > 0) {
                $signe = substr($hint->getPenalty(), 0, 1);

                if ($signe == '-') {
                    $penalty += substr($hint->getPenalty(), 1);
                } else {
                    $penalty += $hint->getPenalty();
                }
            }
        }

        return $penalty;
    }

    public function responseGraphic($request, $paperID = 0)
    {
        $point = 0;
        $answers = $request->request->get('answers');
        $graphId = $request->request->get('graphId');
        $max = $request->request->get('nbpointer');
        $em = $this->doctrine->getEntityManager();
        $rightCoords = $em->getRepository('UJMExoBundle:Coords')->findBy(array('interactionGraphic' => $graphId));
        $verif = array();
        $z = 0;

        $coords = preg_split('[;]', $answers);
        $total = 0;

        for ($i = 0; $i < $max - 1; $i++) {
            for ($j = 0; $j < $max - 1; $j++) {
                list($xa,$ya) = explode("-", $coords[$j]);
                list($xr,$yr) = explode(",", $rightCoords[$i]->getValue());

                $valid = $rightCoords[$i]->getSize() / 2;

                if ((($xa) < ($xr + $valid)) && (($xa) > ($xr - $valid)) && (($ya) < ($yr + $valid)) &&
                    (($ya) > ($yr - $valid))
                ) {
                    if ($this->alreadyDone($rightCoords[$i], $verif, $z)) {
                        $point += $rightCoords[$i]->getScoreCoords();
                        $verif[$z] = $rightCoords[$i];
                        $z++;
                    }
                }
            }
            $total += $rightCoords[$i]->getScoreCoords();
        }

        $interG = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($graphId);
        $doc = $em->getRepository('UJMExoBundle:Document')->findOneBy(array('id' => $interG->getDocument()));

        $penalty = 0;

        $session = $request->getSession();

        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {

                    $signe = substr($penal, 0, 1);

                    if ($signe == '-') {
                        $penalty += substr($penal, 1);
                    } else {
                        $penalty += $penal;
                    }
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenalty($interG->getInteraction(), $paperID);
        }

        $score = $point - $penalty;
        if ($score < 0) {
            $score = 0;
        }

        $res = array(
            'point' => $point,
            'penalty' => $penalty,
            'interG' => $interG,
            'coords' => $rightCoords,
            'doc' => $doc,
            'total' => $total,
            'rep' => $coords,
            'score' => $score,
            'response' => $answers
        );

        return $res;
    }

    public function alreadyDone($coor, $verif, $z)
    {
        $resu = true;

        for ($v = 0; $v < $z; $v++) {
            if ($coor == $verif[$v]) {
                $resu = false;
                break;
            } else {
                $resu = true;
            }
        }

        return $resu;
    }
}