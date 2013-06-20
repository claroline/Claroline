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

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Entity\User;

class InteractionGraphicType extends AbstractType
{

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $id = $this->user->getId();

        $builder
            ->add(
                'interaction', new InteractionType($this->user)
            )
            ->add(
                'document', 'entity', array(
                    'class' => 'UJMExoBundle:Document',
                    'property' => 'label',
                  // Request to get the pictures matching to the user_id
                    'query_builder' => function (\UJM\ExoBundle\Repository\DocumentRepository $repository) use ($id) {
                        return $repository->createQueryBuilder('d')
                            ->where('d.user = ?1')
                            ->andwhere('d.type = \'.png\' OR d.type = \'.jpeg\' OR d.type = \'.jpg\' OR d.type = \'.gif\' OR d.type = \'.bmp\'')
                            ->setParameter(1, $id)
                        ;
                    },
                )
            );
    }

    public function getName()
    {
        return 'ujm_exobundle_interactiongraphictype';
    }
}