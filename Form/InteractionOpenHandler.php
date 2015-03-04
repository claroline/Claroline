<?php

namespace UJM\ExoBundle\Form;

use UJM\ExoBundle\Entity\InteractionOpen;


class InteractionOpenHandler extends \UJM\ExoBundle\Form\InteractionHandler
{

     /**
     * Implements the abstract method
     *
     * @access public
     *
     */
    public function processAdd()
    {
        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);
            $verifCat =$this->form->getData()->getInteraction()->getQuestion()->getCategory();
            $user=$this->form->getData()->getInteraction()->getQuestion()->getUser();
            if($verifCat== null)
            {             
                
                $categoryList = $repositoryCategory->findAll();
                foreach ($categoryList as $category) {
//                    if($category->getUser == $user )
//                    {
//                        if()
//                    }
                    var_dump($category);
                }
//               var_dump($verifCat);
//               die(); 
            }
            if($this->validateNbClone() === FALSE) {
                    return 'infoDuplicateQuestion';
            }
            if ( $this->form->isValid() ) {
                $this->onSuccessAdd($this->form->getData());
                return true;
            }
        }

        return false;
    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
     */
    protected function onSuccessAdd($interOpen)
    {
        $interOpen->getInteraction()->getQuestion()->setDateCreate(new \Datetime());
        $interOpen->getInteraction()->getQuestion()->setUser($this->user);
        $interOpen->getInteraction()->setType('InteractionOpen');

        $this->em->persist($interOpen);
        $this->em->persist($interOpen->getInteraction()->getQuestion());
        $this->em->persist($interOpen->getInteraction());

        $this->persistHints($interOpen);

        $this->em->flush();

        $this->addAnExericse($interOpen);

        $this->duplicateInter($interOpen);

    }

    /**
     * Implements the abstract method
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\InteractionOpen $originalInterOpen
     *
     * Return boolean
     */
    public function processUpdate($originalInterOpen)
    {
        $originalHints = array();

        foreach ($originalInterOpen->getInteraction()->getHints() as $hint) {
            $originalHints[] = $hint;
        }

        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                $this->onSuccessUpdate($this->form->getData(), $originalHints);

                return true;
            }
        }

        return false;
    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function onSuccessUpdate()
    {
        $arg_list = func_get_args();
        $interOpen = $arg_list[0];
        $originalHints = $arg_list[1];

        $this->modifyHints($interOpen, $originalHints);

        $this->em->persist($interOpen);
        $this->em->persist($interOpen->getInteraction()->getQuestion());
        $this->em->persist($interOpen->getInteraction());

        $this->em->flush();

    }
}