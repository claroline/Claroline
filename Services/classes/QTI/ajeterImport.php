<?php

/**
     *
     * Edited by :Hamza
     * ListQuestions
     *
     */
    public function importAction()
    {
        $userDir = './uploads/ujmexo/qti/'.$this->container->get('security.context')
                        ->getToken()->getUser()->getUsername().'/';

      if (!is_dir('./uploads/ujmexo/')) {
        mkdir('./uploads/ujmexo/');
      }
      if (!is_dir('./uploads/ujmexo/qti/')) {
        mkdir('./uploads/ujmexo/qti/');
      }
      if (!is_dir($userDir)) {
        mkdir($userDir);
      }

                  $allowedExts = array("xml");
                  $temp = explode(".", $_FILES["f1"]["name"]);
                  $source = $_FILES["f1"]["tmp_name"];
                  $extension = end($temp);
                  $rst= "src tmp_name : ".$source;
                  $rst= $rst."test rst";
                  if ((($_FILES["f1"]["type"] == "text/xml")) && ($_FILES["f1"]["size"] < 20000000) && in_array($extension, $allowedExts)) {


                                if ($_FILES["f1"]["error"] > 0) {
                                  $rst =$rst . "Return Code: " . $_FILES["f1"]["error"] . "<br/>";
                                } else {
                                  $rst =$rst . "File: " . $_FILES["f1"]["name"] . "\n";
                                  $rst =$rst . "Type: " . $_FILES["f1"]["type"] . "\n";
                                  $rst =$rst . "Size: " . ($_FILES["f1"]["size"] / 1024) . " kB\n";
                                  if (file_exists("upload/" . $_FILES["f1"]["name"])) {
                                    $rst =$rst . $_FILES["f1"]["name"] . " already exists. ";
                                  } else {
                                    move_uploaded_file($_FILES["f1"]["tmp_name"],
                                    $userDir . $_FILES["f1"]["name"]);
                                    $rst =$rst . "Stored in: " . "uploadfiles/" . $_FILES["f1"]["name"];
                                  }
                                }

                                //import xml file
                                $file = $userDir.$_FILES["f1"]["name"];
                                $document_xml = new \DomDocument();
                                $document_xml->load($file);
                                $elements = $document_xml->getElementsByTagName('assessmentItem');
                                $element = $elements->item(0); // On obtient le nœud assessmentItem
                                //$childs = $element->childNodes;
                                if ($element->hasAttribute("title")) {
                                    $title = $element->getAttribute("title");
                                }
                                //get the type of the QCM choiceMultiple or choice
                                $typeqcm = $element->getAttribute("identifier");

                                //Import for Question QCM
                                if($typeqcm=="choiceMultiple" || $typeqcm=="choice" ){
                                            $nodeList=$element->getElementsByTagName("responseDeclaration");
                                            $responseDeclaration=($nodeList->item(0));
                                            $nodeList2=$responseDeclaration->getElementsByTagName("correctResponse");
                                            $correctResponse=$nodeList2->item(0);
                                            $nodelist3 = $correctResponse->getElementsByTagName("value");
                                            $lstmapping = $responseDeclaration->getElementsByTagName('mapping');
                                            $mapEntrys=null;
                                            $baseValue =null;
                                            $baseValue2=null;
                                            if($responseDeclaration->getElementsByTagName("mapping")->item(0)){
                                                $mapping   = $responseDeclaration->getElementsByTagName("mapping")->item(0);
                                                $mapEntrys = $mapping->getElementsByTagName("mapEntry");
                                            }
                                            else
                                                {
                                                $responseProcessing   = $element->getElementsByTagName("responseProcessing")->item(0);
                                                $responseCondition = $responseProcessing->getElementsByTagName("responseCondition")->item(0);
                                                $responseIf = $responseProcessing->getElementsByTagName("responseIf")->item(0);
                                                $setOutcomeValue = $responseIf->getElementsByTagName("setOutcomeValue")->item(0);
                                                $baseValue = $setOutcomeValue->getElementsByTagName("baseValue")->item(0)->nodeValue;
                                                //echo $baseValue;
                                                $responseElse = $responseProcessing->getElementsByTagName("responseElse")->item(0);
                                                $setOutcomeValue = $responseElse->getElementsByTagName("setOutcomeValue")->item(0);
                                                $baseValue2 = $setOutcomeValue->getElementsByTagName("baseValue")->item(0)->nodeValue;
                                                //echo $baseValue2;

                                            }


                                            $modalfeedback=null;
                                            if($element->getElementsByTagName("modalFeedback")->item(0)){

                                                $modalfeedback=$element->getElementsByTagName("modalFeedback");
                                            }

                                            //array correct choices
                                            $correctchoices = new \Doctrine\Common\Collections\ArrayCollection;

                                            foreach($nodelist3 as $value)
                                            {
                                                $valeur = $value->nodeValue."\n";
                                                $correctchoices->add($valeur);
                                                //$rst =$rst."--------value : ".$valeur."\n";
                                            }
                                            if($element->getElementsByTagName("outcomeDeclaration")->item(0)){
                                                $nodeList=$element->getElementsByTagName("outcomeDeclaration");
                                                $responseDeclaration=($nodeList->item(0));
                                                $nodeList2=$responseDeclaration->getElementsByTagName("defaultValue");
                                                $correctResponse=$nodeList2->item(0);
                                                $nodelist3 = $correctResponse->getElementsByTagName("value");

                                                foreach($nodelist3 as $score)
                                                {
                                                    $valeur = $score->nodeValue."\n";
                                                    $rst =$rst."--------score : ".$valeur."\n";
                                                }
                                            }


                                            $nodeList=$element->getElementsByTagName("itemBody");
                                            $itemBody=($nodeList->item(0));
                                            $nodeList2=$itemBody->getElementsByTagName("choiceInteraction");
                                            $choiceInteraction=$nodeList2->item(0);
                                            //question
                                            $prompt=null;
                                            if($choiceInteraction->getElementsByTagName("prompt")->item(0)){
                                                $prompt = $choiceInteraction->getElementsByTagName("prompt")->item(0)->nodeValue;
                                            }else{
                                                $prompt= $title;
                                            }
                                            //$rst =$rst."--------prompt : ".$prompt."\n";



                                            //array correct choices
                                            $choices = new \Doctrine\Common\Collections\ArrayCollection;
                                            $commentsperline = new \Doctrine\Common\Collections\ArrayCollection;

                                            $identifier_choices = new \Doctrine\Common\Collections\ArrayCollection;

                                            $nodeList3=$choiceInteraction->getElementsByTagName("simpleChoice");
                                            $rst="";
                                            foreach($nodeList3 as $simpleChoice)
                                            {

                                                if($simpleChoice->getElementsByTagName("feedbackInline")->item(0)){
                                                     $feedbackInline = $simpleChoice->getElementsByTagName("feedbackInline")->item(0)->nodeValue;
                                                     $feedback = $simpleChoice->getElementsByTagName("feedbackInline")->item(0);
                                                     $choicestest= $simpleChoice->removeChild($feedback);
                                                     $commentsperline->add($feedbackInline);
                                                }
                                                $choices->add($simpleChoice->nodeValue);
                                                $identifier_choices->add($simpleChoice->getAttribute("identifier"));

                                                //test
                                                //$feedback= $simpleChoice->getElementsByTagName("feedbackInline")->item(0);
                                                $rst =$rst."--_-_-_-_---removetst----Choice : ".$simpleChoice->nodeValue ."\n";
                                                //$rst =$rst."-_-_-removetst-end_-_-";
                                                // $feedbackInline = $feedback->nodeValue;
                                                //$rst =$rst."--_-_-_-_---removetst----feedback : ".$feedbackInline."\n";
                                                //$rst =$rst."--------identifier ".$identifier."\n";
                                            }


                                            //add the question o the database :

                                            $question  = new Question();
                                            $Category = new Category();
                                            $interaction =new Interaction();
                                            $interactionqcm =new InteractionQCM();




                                            //question & category
                                            $question->setTitle($title);
                                            //check if the Category "Import" exist --else-- will create it
                                            $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));
                                            if(count($Category_import)==0){
                                                $Category->setValue("import");
                                                $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                                $question->setCategory($Category);
                                            }else{
                                                $question->setCategory($Category_import[0]);
                                            }


                                            $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $date = new \Datetime();
                                            $question->setDateCreate(new \Datetime());



                                            //Interaction

                                            $interaction->setType('InteractionQCM');
                                            if($prompt!=null){
                                                 $interaction->setInvite($prompt);
                                            }
                                            $interaction->setQuestion($question);
                                            if($modalfeedback!=null){
                                                if(($modalfeedback->item(0)->nodeValue != null) && ($modalfeedback->item(0)->nodeValue != "")){
                                                $interaction->setFeedBack($modalfeedback->item(0)->nodeValue);
                                                }
                                            }







                                            $em = $this->getDoctrine()->getManager();


                                            $ord=1;
                                            $index =0;
                                            foreach ($choices as $choix) {
                                                //choices
                                                $choice1 = new Choice();
                                                $choice1->setLabel($choix);
                                                $choice1->setOrdre($ord);

                                                //add Mappentry
                                                $weight =False;
                                                if($mapEntrys!=null){
                                                    if(count($mapEntrys)>0){
                                                    $mapEntry=$mapEntrys->item($index);
                                                    $mappedValue=$mapEntry->getAttribute("mappedValue");
                                                    //$mapKey=$mapEntry->getAttribute("mappedValue");
                                                    $choice1->setWeight($mappedValue);
                                                    $interactionqcm->setWeightResponse(1);
                                                    $weight =True;
                                                    }
                                                     $interactionqcm->setScoreRightResponse(0);
                                                     $interactionqcm->setScoreFalseResponse(0);
                                                }else{

                                                    $interactionqcm->setScoreFalseResponse($baseValue2);
                                                    $interactionqcm->setScoreRightResponse($baseValue);
                                                    $interactionqcm->setWeightResponse(0);
                                                }


                                                //
                                                //add comment
                                                if(count($commentsperline)>0){
                                                    $choice1->setFeedback($commentsperline[$index]);
                                                }
                                                //
                                                foreach ($correctchoices as $corrvalue) {
                                                    $rst= $rst."------------".$identifier_choices[$index]."*--------------------".$corrvalue;
                                                    if(strtolower(trim($identifier_choices[$index])) == strtolower(trim($corrvalue))){
                                                        $rst= $rst."***********".$identifier_choices[$index]."***********".$corrvalue;
                                                        $choice1->setRightResponse(TRUE);
                                                    }
                                                }
                                                $interactionqcm->addChoice($choice1);
                                                $em->persist($choice1);
                                                $ord=$ord+1;
                                                $index=$index+1;
                                            }
                                            //InteractionQCM
                                            $type_qcm = $this->getDoctrine()
                                                        ->getManager()
                                                        ->getRepository('UJMExoBundle:TypeQCM')->findAll();
                                            if($typeqcm=="choice"){
                                                $interactionqcm->setTypeQCM($type_qcm[1]);
                                            }else{
                                                $interactionqcm->setTypeQCM($type_qcm[0]);
                                            }
                                            $interactionqcm->setInteraction($interaction);




                                            $em->persist($interactionqcm);
                                            $em->persist($interactionqcm->getInteraction()->getQuestion());
                                            $em->persist($interactionqcm->getInteraction());

                                            if(count($Category_import)==0){
                                            $em->persist($Category);
                                            }
                                                //echo($choice->getRightResponse());


                                            $em->flush();
                                }

                  } else {
                    $rst =$rst . "Invalid file";
                  }
                    $rst = $rst . dirname(__FILE__).'/'."\n";

                   //if it's QTI zip file  --> unzip the file into this path "/var/www/Claroline/web/uploadfiles/" --> add to the database the resources (images)
                  if(($_FILES["f1"]["type"] == "application/zip") && ($_FILES["f1"]["size"] < 20000000)){

                      $rst = 'its a zip file';
                      move_uploaded_file($_FILES["f1"]["tmp_name"],
                                $userDir . $_FILES["f1"]["name"]);
                      $zip = new \ZipArchive;
                      $zip->open($userDir . $_FILES["f1"]["name"]);
                      $res= zip_open($userDir . $_FILES["f1"]["name"]);

                      $zip->extractTo($userDir);
                      $tab_liste_fichiers = array();
                      while ($zip_entry = zip_read($res)) //Pour chaque fichier contenu dans le fichier zip
                        {
                            if(zip_entry_filesize($zip_entry) > 0)
                            {
                                $nom_fichier = zip_entry_name($zip_entry);
                                $rst =$rst . '-_-_-_'.$nom_fichier;
                                array_push($tab_liste_fichiers,$nom_fichier);

                            }
                        }

                      $zip->close();



                        //Import for Question QCM --> from unZip File --> Type choiceMultiple Or  choice
                        //import xml file
                                $file = "$userDir/SchemaQTI.xml";
                                $document_xml = new \DomDocument();
                                $document_xml->load($file);
                                $elements = $document_xml->getElementsByTagName('assessmentItem');
                                $element = $elements->item(0); // On obtient le nœud assessmentItem
                                //$childs = $element->childNodes;
                                if ($element->hasAttribute("title")) {
                                    $title = $element->getAttribute("title");
                                }
                                //get the type of the QCM choiceMultiple or choice
                                $typeqcm = $element->getAttribute("identifier");
                                //echo $typeqcm;
                                if(($typeqcm=="choiceMultiple") || ($typeqcm=="choice") ){

                                                                           //début : récupération des fichiers et stocker les images dans les tables File
                                                                                //creation of the ResourceNode & File for the images...
                                                                                $user= $this->container->get('security.context')->getToken()->getUser();
                                                                                //createur du workspace
                                                                                $workspace = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findBy(array('creator' => $user->getId()));
                                                                                //$directory = $this->getReference("directory/{$this->directory}");
                                                                                //$directory = $this->get('claroline.manager.resource_manager');
                                                                                $resourceManager = $this->container->get('claroline.manager.resource_manager');
                                                                                $filesDirectory = $this->container->getParameter('claroline.param.files_directory');
                                                                                $ut = $this->container->get('claroline.utilities.misc');
                                                                                $fileType = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('file');
                                                                                $rst =$rst .'---wrkspace----'.$workspace[0]->getName().'-------------';

                                                                                $liste_resource_idnode = array();
                                                                                foreach ($tab_liste_fichiers as $filename) {

                                                                                    //filepath contain the path of the files in the extraction palce "uploadfile"
                                                                                    $filePath = $userDir.$filename;
                                                                                    $filePathParts = explode(DIRECTORY_SEPARATOR, $filePath);
                                                                                    //file name of the file
                                                                                    $fileName = array_pop($filePathParts);
                                                                                    //extension of the file
                                                                                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                                                                                    $hashName = "{$ut->generateGuid()}.{$extension}";

                                                                                    $targetFilePath = $filesDirectory . DIRECTORY_SEPARATOR . $hashName;
                                                                                    //$directory = $this->getReference($filesDirectory);

                                                                                    $file = new \Claroline\CoreBundle\Entity\Resource\File();
                                                                                    $file->setName($fileName);
                                                                                    $file->setHashName($hashName);

                                                                                    $rst =$rst . '-_-hashname_-_'.$hashName.'--extention---'.$extension.'--targetFilePath---'.$targetFilePath;
                                                                                    if(($extension=='jpg')||($extension=='jpeg')||($extension=='gif')){
                                                                                        if (file_exists($filePath)) {
                                                                                            copy($filePath, $targetFilePath);
                                                                                            $file->setSize(filesize($filePath));
                                                                                        } else {
                                                                                            touch($targetFilePath);
                                                                                            $file->setSize(0);
                                                                                        }
                                                                                        $mimeType = MimeTypeGuesser::getInstance()->guess($targetFilePath);
                                                                                        $rst =$rst . '-_-MimeTypeGuesser-_'.$mimeType;
                                                                                        $file->setMimeType($mimeType);

                                                                                        //creation ressourcenode
                                                            //                            $node = new ResourceNode();
                                                            //                            $node->setResourceType($fileType);
                                                            //                            $node->setCreator($user);
                                                            //                            $node->setWorkspace($workspace[0]);
                                                            //                            $node->setCreationDate(new \Datetime());
                                                            //                            $node->setClass('Claroline\CoreBundle\Entity\Resource\File');
                                                            //                            $node->setName($workspace[0]->getName());
                                                            //                            $node->setMimeType($mimeType);

                                                                                       // $file->setResourceNode($node);

                                                                                        //$this->getDoctrine()->getManager()->persist($node);
                                                                                        $role = $this
                                                                                                    ->getDoctrine()
                                                                                                    ->getRepository('ClarolineCoreBundle:Role')
                                                                                                    ->findManagerRole($workspace[0]);
                                                                                        $rigths = array(
                                                                                             'ROLE_WS_MANAGER' => array('open' => true, 'export' => true, 'create' => array(),
                                                                                                                        'role' => $role
                                                                                                                       )
                                                                                        );
                                                                                        //echo 'ws : '.$user->getPersonalWorkspace()->getName();die();
                                                                                        $parent = $this
                                                                                                    ->getDoctrine()
                                                                                                    ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                                                                                                    ->findWorkspaceRoot($user->getPersonalWorkspace());

                                                                                        $resourceManager->create($file, $fileType, $user, $user->getPersonalWorkspace(), $parent, NULL, $rigths);// ,$node);
                                                                                            //list of the Resource ID Node that already craeted

                                                                                             array_push($liste_resource_idnode,$file->getResourceNode()->getId());

                                                                                    }
                                                                                }
                                                                                 //$file->getResourceNode()->getId()  ;die();
                                                                                $this->getDoctrine()->getManager()->flush();
                                                                      //Fin récupération & stockage
                                            $nodeList=$element->getElementsByTagName("responseDeclaration");
                                            $responseDeclaration=($nodeList->item(0));
                                            $nodeList2=$responseDeclaration->getElementsByTagName("correctResponse");
                                            $correctResponse=$nodeList2->item(0);
                                            $nodelist3 = $correctResponse->getElementsByTagName("value");

                                            //array correct choices
                                            $correctchoices = new \Doctrine\Common\Collections\ArrayCollection;

                                            foreach($nodelist3 as $value)
                                            {
                                                $valeur = $value->nodeValue."\n";
                                                $correctchoices->add($valeur);
                                                //$rst =$rst."--------value : ".$valeur."\n";
                                            }

                                            $nodeList=$element->getElementsByTagName("outcomeDeclaration");
                                            $responseDeclaration=($nodeList->item(0));
                                            $nodeList2=$responseDeclaration->getElementsByTagName("defaultValue");
                                            $correctResponse=$nodeList2->item(0);
                                            $nodelist3 = $correctResponse->getElementsByTagName("value");

                                            foreach($nodelist3 as $score)
                                            {
                                                $valeur = $score->nodeValue."\n";
                                                $rst =$rst."--------score : ".$valeur."\n";
                                            }

                                            $nodeList=$element->getElementsByTagName("itemBody");
                                            $itemBody=($nodeList->item(0));
                                            $nodeList2=$itemBody->getElementsByTagName("choiceInteraction");
                                            $choiceInteraction=$nodeList2->item(0);
                                            //question
                                            if($choiceInteraction->getElementsByTagName("prompt")->item(0)){
                                                $prompt = $choiceInteraction->getElementsByTagName("prompt")->item(0)->nodeValue;
                                                //change the src of the image :by using this path with integrating the resourceIdNode "/Claroline/web/app_dev.php/file/resource/media/5"
                                                            $dom2 = new \DOMDocument();
                                                            $dom2->loadHTML(html_entity_decode($prompt));
                                                            $listeimgs = $dom2->getElementsByTagName("img");
                                                            $index = 0;
                                                            foreach($listeimgs as $img)
                                                            {
                                                              if ($img->hasAttribute("src")) {
                                                                 $img->setAttribute("src","/Claroline/web/app_dev.php/file/resource/media/".$liste_resource_idnode[$index]);
                                                              }
                                                             $index= $index +1;
                                                            }
                                                            $res_prompt = $dom2->saveHTML();
                                                           // echo htmlentities($res);
                                            //$rst =$rst."--------prompt : ".$prompt."\n";
                                            }else{
                                                $res_prompt= $title;
                                            }



                                            //array correct choices
                                            $choices = new \Doctrine\Common\Collections\ArrayCollection;
                                            $identifier_choices = new \Doctrine\Common\Collections\ArrayCollection;

                                            $nodeList3=$choiceInteraction->getElementsByTagName("simpleChoice");
                                            foreach($nodeList3 as $simpleChoice)
                                            {
                                                $choices->add($simpleChoice->nodeValue);
                                                $identifier_choices->add($simpleChoice->getAttribute("identifier"));
                                                //$rst =$rst."--------Choice : ".$valeur."\n";
                                                //$identifier =
                                                //$rst =$rst."--------identifier ".$identifier."\n";
                                            }


                                            //add the question o the database :

                                            $question  = new Question();
                                            $Category = new Category();
                                            $interaction =new Interaction();
                                            $interactionqcm =new InteractionQCM();




                                            //question & category
                                            $question->setTitle($title);
                                            //check if the Category "Import" exist --else-- will create it
                                            $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));

                                            if(count($Category_import)==0){
                                                $Category->setValue("import");
                                                $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                                $question->setCategory($Category);
                                            }else{
                                                $question->setCategory($Category_import[0]);
                                            }


                                            $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $date = new \Datetime();
                                            $question->setDateCreate(new \Datetime());



                                            //Interaction

                                            $interaction->setType('InteractionQCM');
                                            //strip_tags($res_prompt,'<img><a><p><table>')
                                            $interaction->setInvite(($res_prompt));
                                            $interaction->setQuestion($question);







                                            $em = $this->getDoctrine()->getManager();


                                            $ord=1;
                                            $index =0;
                                            foreach ($choices as $choix) {
                                                //choices
                                                $choice1 = new Choice();
                                                $choice1->setLabel($choix);
                                                $choice1->setOrdre($ord);
                                                foreach ($correctchoices as $corrvalue) {
                                                    $rst= $rst."------------".$identifier_choices[$index]."*--------------------".$corrvalue;
                                                    if(strtolower(trim($identifier_choices[$index])) == strtolower(trim($corrvalue))){
                                                        $rst= $rst."***********".$identifier_choices[$index]."***********".$corrvalue;
                                                        $choice1->setRightResponse(TRUE);
                                                    }
                                                }
                                                $interactionqcm->addChoice($choice1);
                                                $em->persist($choice1);
                                                $ord=$ord+1;
                                                $index=$index+1;
                                            }
                                            //InteractionQCM
                                            $type_qcm = $this->getDoctrine()
                                                        ->getManager()
                                                        ->getRepository('UJMExoBundle:TypeQCM')->findAll();
                                            if($typeqcm=="choice"){
                                                $interactionqcm->setTypeQCM($type_qcm[1]);
                                            }else{
                                                $interactionqcm->setTypeQCM($type_qcm[0]);
                                            }
                                            $interactionqcm->setInteraction($interaction);


                                            $em->persist($interactionqcm);
                                            $em->persist($interactionqcm->getInteraction()->getQuestion());
                                            $em->persist($interactionqcm->getInteraction());

                                            if(count($Category_import)==0){
                                            $em->persist($Category);
                                            }
                                                //echo($choice->getRightResponse());


                                            $em->flush();
                                }else if($typeqcm=="SelectPoint"){
                                            $rst= $rst. "enter";
                                        $responsedaclr = $element->getElementsByTagName("responseDeclaration");
                                        //$responsedaclr = $elements->item(0);
                                        $nodelist = $responsedaclr->item(0);
                                        $correctresponse = $nodelist->getElementsByTagName("correctResponse");

                                        //echo $correctresponse->nodeValue;
                                        //$valeur = $nodelist->getElementByTagName("value");
                                        $areaMapping=$responsedaclr->item(0)->getElementsByTagName("areaMapping");
                                        $areaMapEntry =$areaMapping->item(0)->getElementsByTagName("areaMapEntry");
                                        $shape =$areaMapEntry->item(0)->getAttribute("shape");
                                        $coordstxt=$areaMapEntry->item(0)->getAttribute("coords");
                                        $mappedValue=$areaMapEntry->item(0)->getAttribute("mappedValue");

                                        $itemBody= $element->getElementsByTagName("itemBody");
                                        $selectPointInteraction = $itemBody->item(0)->getElementsByTagName("selectPointInteraction");
                                        $prompt =  $selectPointInteraction->item(0)->getElementsByTagName("prompt");
                                        $object = $selectPointInteraction->item(0)->getElementsByTagName("object");


                                        $type=$object->item(0)->getAttribute("type");
                                        $width=$object->item(0)->getAttribute("width");
                                        $height=$object->item(0)->getAttribute("height");
                                        $data=$object->item(0)->getAttribute("data");

                                        $modalfeedback=null;
                                        if($element->getElementsByTagName("modalFeedback")->item(0)){

                                            $modalfeedback=$element->getElementsByTagName("modalFeedback");
                                        }



                                        $question  = new Question();
                                        $Category = new Category();
                                        $interaction =new Interaction();
                                        $interactiongraphic =new InteractionGraphic();
                                        $coords = new Coords();
                                        $ujmdocument = new Document();



                                        $question->setTitle($title);
                                        $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));

                                        if(count($Category_import)==0){
                                            $Category->setValue("import");
                                            $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $question->setCategory($Category);
                                        }else{
                                            $question->setCategory($Category_import[0]);
                                        }

                                        $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                        $date = new \Datetime();
                                        $question->setDateCreate(new \Datetime());



                                        $interaction->setType('InteractionGraphic');
                                        //strip_tags($res_prompt,'<img><a><p><table>')
                                        var_dump($modalfeedback->item(0)->nodeValue);
                                        $interaction->setInvite(($prompt->item(0)->nodeValue));
                                        $interaction->setQuestion($question);
                                        $interaction->setFeedBack($modalfeedback->item(0)->nodeValue);

                                        $interactiongraphic->setWidth($width);
                                        $interactiongraphic->setHeight($height);



                                        //list($x,$y,$z) = split('[,]', $coords);
                                        $parts = explode(",", $coordstxt);
                                        $x = $parts[0];
                                        $y = $parts[1];
                                        $z = $parts[2];
                                        $radius = $z * 2;
                                        $x_center=$x - ($radius);
                                        $y_center=$y - ($radius);

                                        $coords->setShape($shape);var_dump($shape);
                                        $coords->setValue($x_center.",".$y_center);
                                        $coords->setSize($radius);
                                        $coords->setColor('white');
                                        $coords->setInteractionGraphic($interactiongraphic);
                                        $coords->setScoreCoords($mappedValue);var_dump($mappedValue);

                                        $user= $this->container->get('security.context')->getToken()->getUser();
                                        $ujmdocument->setUser($user);
                                        $ujmdocument->setLabel($object->item(0)->nodeValue);var_dump($object->item(0)->nodeValue);
                                            //file name of the file
                                            $listpath = explode("/", $data);
                                            $fileName =  $listpath[count($listpath)-1];
                                            $rst=$rst."$fileName=". $fileName;

                                            //extension of the file
                                            $extension = pathinfo($data, PATHINFO_EXTENSION);
                                        //il faut changer le nom de l'image
                                        $ujmdocument->setUrl("./uploads/ujmexo/users_documents/".$user->getUsername()."/images/".$fileName);
                                        $ujmdocument->setType($extension);



                                        $interactiongraphic->setInteraction($interaction);
                                        $interactiongraphic->setDocument($ujmdocument);


                                        $em = $this->getDoctrine()->getManager();

                                        $em->persist($coords->getInteractionGraphic());
                                        $em->persist($ujmdocument);
                                        $em->persist($coords);
                                        $em->persist($coords->getInteractionGraphic()->getInteraction()->getQuestion());
                                        $em->persist($coords->getInteractionGraphic()->getInteraction());

                                        if(count($Category_import)==0){
                                            $em->persist($Category);
                                        }

                                        $em->flush();


                                }else if($typeqcm=="extendedText"){

                                        $responsedaclr = $element->getElementsByTagName("responseDeclaration");
                                        //$responsedaclr = $elements->item(0);
                                        $nodelist = $responsedaclr->item(0);
                                        //$correctresponse = $nodelist->getElementsByTagName("correctResponse");

                                        //echo $correctresponse->nodeValue;
                                        //$valeur = $nodelist->getElementByTagName("value");
                                        $outcomeDeclaration=$element->getElementsByTagName("outcomeDeclaration");
                                        $defaultValue =$outcomeDeclaration->item(0)->getElementsByTagName("defaultValue");
                                        $value =$defaultValue->item(0)->getAttribute("value");



                                        $itemBody= $element->getElementsByTagName("itemBody");

                                        $modalfeedback=null;
                                        if($element->getElementsByTagName("modalFeedback")->item(0)){

                                            $modalfeedback=$element->getElementsByTagName("modalFeedback");
                                        }



                                        $question  = new Question();
                                        $Category = new Category();
                                        $interaction =new Interaction();
                                        $InteractionOpen =new InteractionOpen();



                                        $InteractionOpen->setOrthographyCorrect(0);

                                        $question->setTitle($title);
                                        $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));

                                        if(count($Category_import)==0){
                                            $Category->setValue("import");
                                            $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $question->setCategory($Category);
                                        }else{
                                            $question->setCategory($Category_import[0]);
                                        }

                                        $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                        $date = new \Datetime();
                                        $question->setDateCreate(new \Datetime());



                                        $interaction->setType('InteractionOpen');
                                        //strip_tags($res_prompt,'<img><a><p><table>')

                                        $interaction->setInvite(($itemBody->item(0)->nodeValue));
                                        $interaction->setQuestion($question);
                                        $interaction->setFeedBack($modalfeedback->item(0)->nodeValue);


                                        $user= $this->container->get('security.context')->getToken()->getUser();


                                        $InteractionOpen->setInteraction($interaction);


                                        $em = $this->getDoctrine()->getManager();

                                        $em->persist($InteractionOpen);
                                        $em->persist($question);
                                        $em->persist($interaction);

                                        if(count($Category_import)==0){
                                            $em->persist($Category);
                                        }

                                        $em->flush();


                                }else if($typeqcm=="entrytext"){




                                                //import entrytext question "Question à trou"


                                }


                  }


               /*
                foreach($childs as $enfant) // On prend chaque nœud enfant séparément
                {

                    /*   //$value = $enfant->nodeValue;
                      $nom = $enfant->nodeName; // On prend le nom de chaque nœud
                      $rst =$rst . $nom."<br/>".$value."</br>";
                    if($enfant->hasChildNodes() == true){
                        $childs_level2 = $enfant->childNodes;
                        foreach($childs_level2 as $enfant_l2) // On prend chaque nœud enfant séparément
                        {
                            $enfant_l2->
                            $value = $enfant_l2->nodeValue;
                            $nom = $enfant_l2->nodeName; // On prend le nom de chaque nœud
                            $rst =$rst . $nom."<br/>".$value."</br>";
                        }
                    }


                }   return $this->render('UJMExoBundle:Question:index.html.twig');

                */

                $this->removeDirectory($userDir);
                $response = $this->forward('UJMExoBundle:Question:index', array());

                return $response;
//                return $this->render(
//                      'UJMExoBundle:Question:index.html.twig', array(
//                      'rst' => $rst,
//                      )
//                );


    }