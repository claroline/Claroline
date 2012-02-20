<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\ProfileType;

class UserProfileController extends Controller
{
    public function showEditProfileAction()
    {
        $user = new User();
        $formUserProfile = $this->createForm(new ProfileType(), $user);
        return $this->render(
        'ClarolineCoreBundle:UserProfile:show_edit.html.twig', array(
            'formuser' => $formUserProfile->createView()));
    }
    
    public function editProfileAction()
    {
        return new Response ("lol");
       
    }
    
    public function showProfileAction()
    {
           return new Response ("lol");
    }

}

?>
