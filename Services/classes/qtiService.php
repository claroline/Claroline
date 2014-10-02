<?php

namespace UJM\ExoBundle\Services\classes;


use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;


class qtiService
{
    protected $doctrine;
    protected $securityContext;
    private $userDir;
    private $node;


    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext Dependency Injection
     *
     */
    public function __construct(Registry $doctrine, SecurityContextInterface $securityContext)
    {
        $this->doctrine        = $doctrine;
        $this->securityContext = $securityContext;
        $this->userDir = './uploads/ujmexo/qti/'
                .$this->securityContext->getToken()
                ->getUser()->getUsername().'/';
    }

    /**
     * To export a question in QTI
     * 
     *  @access public
     *
     * @param integer $questionId id of question
     * 
     * return file
     *
     */
    public function export($questionId)
    {

        $this->createDirQTI();

        $interaction = $this->doctrine
                            ->getManager()
                            ->getRepository('UJMExoBundle:Interaction')
                            ->getInteraction($questionId);
            
        $Question = $interaction->getQuestion();

        $typeInter = $interaction->getType();

            switch ($typeInter) {
                case "InteractionQCM":

                $interactionsqcm = $this->doctrine
                                        ->getManager()
                                        ->getRepository('UJMExoBundle:InteractionQCM')->findBy(array('interaction' => $interaction->getId()));

                //if it's Null mean "Global notation for QCM" Else it's Notation for each choice
                $weightresponse = $interactionsqcm[0]->getWeightResponse();

                $choices2 = $interactionsqcm[0]->getChoices();

                // Search for the ID of the ressource from the Invite colonne
                $txt  = $interaction->getInvite();

                $path_img="";

                $dom2 = new \DOMDocument();
                $dom2->loadHTML(html_entity_decode($txt));
                $listeimgs = $dom2->getElementsByTagName("img");
                foreach($listeimgs as $img)
                {
                  if ($img->hasAttribute("src")) {
                     $src= $img->getAttribute("src");
                     $id_node= substr($src, 47);
                     $resources_file = $this->doctrine
                                   ->getManager()
                                   ->getRepository('ClarolineCoreBundle:Resource\File')->findBy(array('resourceNode' => $id_node));
                     $resources_node = $this->doctrine
                                   ->getManager()
                                   ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findBy(array('id' => $id_node));
                     $path_img = $this->container->getParameter('claroline.param.files_directory').'/'.$resources_file[0]->getHashName();
                  }

                }

                $Alphabets = array('A','B','C','D','E','F','G','H','I','G','K','L');

                $document = new \DOMDocument();

                $this->qtiHead($document, 'choice', $Question->getTitle());
                $responseDeclaration = $this->qtiResponseDeclaration($document, 'identifier');
                $outcomeDeclaration = $this->qtiOutComeDeclaration($document);

                //add the tag <Default value> to the item <outcomeDeclaration>
                $defaultValue = $document->CreateElement('defaultValue');
                $outcomeDeclaration->appendChild($defaultValue);
                $value = $document->CreateElement("value");
                $prompttxt =  $document->CreateTextNode("0");
                $value->appendChild($prompttxt);
                $defaultValue->appendChild($value);

                $correctResponse = $document->CreateElement('correctResponse');
                $responseDeclaration->appendChild($correctResponse);

                $itemBody = $document->CreateElement('itemBody');
                $this->node->appendChild($itemBody);

                $choiceInteraction = $document->CreateElement('choiceInteraction');
                $choiceInteraction->setAttribute("responseIdentifier", "RESPONSE");
                if($interactionsqcm[0]->getShuffle()==1){
                    $boolval = "true";
                }else $boolval = "false";

                $choiceInteraction->setAttribute("shuffle",$boolval);
                $choiceInteraction->setAttribute("maxChoices", "1");
                $itemBody->appendChild($choiceInteraction);

                $prompt = $document->CreateElement('prompt');
                $choiceInteraction->appendChild($prompt);

                //Code pour eliminer du code html sauf la balise img
                $res1 =strip_tags($interaction->getInvite(), '<img>');
                if(!empty($path_img)){
                    //expression regulière pour eliminer tous les attributs des balises
                    $reg="#(?<=\<img)\s*[^>]*(?=>)#";
                    $res1=preg_replace($reg,"",$res1);
                    //rajouter src de l'image
                    $res1= str_replace("<img>", "<img src=\"".$resources_node[0]->getName()."\" alt=\"\" />",$res1);
                    //generate the mannifest file
                    $this->generate_imsmanifest_File($resources_node[0]->getName());
                }

                $mapping = $document->CreateElement('mapping');
                $prompttxt =  $document->CreateTextNode(html_entity_decode($res1));
                $prompt->appendChild($prompttxt);
                $i=-1;
                foreach($choices2 as $ch){

                    $i++;
                    if($ch->getRightResponse()== true){
                            $value = $document->CreateElement('value');
                            $correctResponse->appendChild($value);
                            $valuetxt =  $document->CreateTextNode("Choice".$Alphabets[$i]);
                            $value->appendChild($valuetxt);
                    }
                   //Add new Tag mapping if the weight of the question is true
                   if($weightresponse==true){
                       // Unique Notation for the QCM
                       $mapEntry= $document->CreateElement('mapEntry');
                       $mapEntry->setAttribute("mapKey", "Choice".$Alphabets[$i] );
                       $mapEntry->setAttribute("mappedValue",$ch->getWeight());
                       $mapping->appendChild($mapEntry);
                       $responseDeclaration->appendChild($mapping);
                   }else{
                       // Globale Notation for the QCM
                       $responseProcessing =  $document->CreateElement('responseProcessing');
                       $responseCondition = $document->CreateElement('responseCondition');
                       $responseIf = $document->CreateElement('responseIf');
                       $responseElse = $document->CreateElement('responseElse');
                       $match = $document->CreateElement('match');
                       $variable = $document->CreateElement('variable');
                       $variable->setAttribute("identifier", "RESPONSE");
                       $correct = $document->CreateElement('correct');
                       $correct->setAttribute("identifier", "RESPONSE");

                       $match->appendChild($variable);
                       $match->appendChild($correct);

                       $setOutcomeValue = $document->CreateElement('setOutcomeValue');
                       $setOutcomeValue->setAttribute("identifier", "SCORE");

                       $baseValue= $document->CreateElement('baseValue');
                       $baseValue->setAttribute("baseType", "float");
                       $baseValuetxt = $document->CreateTextNode($interactionsqcm[0]->getScoreRightResponse());
                       $baseValue->appendChild($baseValuetxt);

                       $responseIf->appendChild($match);
                       $setOutcomeValue->appendChild($baseValue);
                       $responseIf->appendChild($setOutcomeValue);

                       ////
                       $setOutcomeValue = $document->CreateElement('setOutcomeValue');
                       $setOutcomeValue->setAttribute("identifier", "SCORE");

                       $baseValue= $document->CreateElement('baseValue');
                       $baseValue->setAttribute("baseType", "float");
                       $baseValuetxt = $document->CreateTextNode($interactionsqcm[0]->getScoreFalseResponse());
                       $baseValue->appendChild($baseValuetxt);


                       $setOutcomeValue->appendChild($baseValue);
                       $responseElse->appendChild($setOutcomeValue);


                       $responseCondition->appendChild($responseIf);
                       $responseCondition->appendChild($responseElse);

                       $responseProcessing->appendChild($responseCondition);



                   }
                   //

                    $simpleChoice = $document->CreateElement('simpleChoice');
                    $simpleChoice->setAttribute("identifier", "Choice".$Alphabets[$i]);
                    $choiceInteraction->appendChild($simpleChoice);
                    $simpleChoicetxt =  $document->CreateTextNode(strip_tags($ch->getLabel(),'<img>'));
                    $simpleChoice->appendChild($simpleChoicetxt);
                    //comment per line for each choice
                    if(($ch->getFeedback()!=Null) && ($ch->getFeedback()!="")){
                           $feedbackInline = $document->CreateElement('feedbackInline');
                           $feedbackInline->setAttribute("outcomeIdentifier", "FEEDBACK");
                           $feedbackInline->setAttribute("identifier","Choice".$Alphabets[$i]);
                           $feedbackInline->setAttribute("showHide","show");
                           $feedbackInlinetxt =  $document->CreateTextNode($ch->getFeedback());
                           $feedbackInline->appendChild($feedbackInlinetxt);
                           $simpleChoice->appendChild($feedbackInline);
                    }

                }

                //comment globale for this question
                if(($interaction->getFeedBack()!=Null) && ($interaction->getFeedBack()!="") ){
                    $this->qtiFeedBack($document, $interaction->getFeedBack());
                }

                if($weightresponse==False){
                    $this->node->appendChild($responseProcessing);
                }

                $document->save($this->userDir.'testfile.xml');

                //sfConfig::set('sf_web_debug', false);
                $tmpFileName = tempnam($this->userDir.'tmp', "xb_");
                $zip = new \ZipArchive();
                $zip->open($tmpFileName, \ZipArchive::CREATE);
                $zip->addFile($this->userDir.'testfile.xml', 'SchemaQTI.xml');

                if(!empty($path_img)){
                     $zip->addFile($path_img, "images/".$resources_node[0]->getName());
                     $zip->addFile($this->userDir.'imsmanifest.xml', 'imsmanifest.xml');
                }
                $zip->close();
                $response = new BinaryFileResponse($tmpFileName);
                //$response->headers->set('Content-Type', $content->getContentType());
                $response->headers->set('Content-Type', 'application/application/zip');
                $response->headers->set('Content-Disposition', "attachment; filename=QTI-Archive.zip");


                return $response;


                case "InteractionGraphic":

                     $interactionGraphic = $this->doctrine
                                                ->getManager()
                                                ->getRepository('UJMExoBundle:InteractionGraphic')->findBy(array('interaction' => $interaction->getId()));

                     $coords = $this->doctrine
                                                ->getManager()
                                                ->getRepository('UJMExoBundle:Coords')->findBy(array('interactionGraphic' => $interactionGraphic[0]->getId()));
                     $Documents = $this->doctrine
                                             ->getManager()
                                             ->getRepository('UJMExoBundle:Document')->findBy(array('id' => $interactionGraphic[0]->getDocument()));


                /*Claculate Radius  and x,y of the center of the circle
                 * rect: left-x, top-y, right-x, bottom-y.
                 * circle: center-x, center-y, radius. Note. When the radius value is a percentage value,
                 */
                 $Coords_value= $coords[0]->getValue();
                 $Coords_size = $coords[0]->getSize();
                 $radius = $Coords_size/2;
                 list($x, $y) = split('[,]', $Coords_value);

                 $x_center_circle=$x + ($radius);
                 $y_center_circle=$y + ($radius);

                //creation of the XML FIle
                     $document = new \DOMDocument();

                // on crée l'élément principal <Node>
                    $node = $document->CreateElement('assessmentItem');
                    $node->setAttribute("xmlns", "http://www.imsglobal.org/xsd/imsqti_v2p1");
                    $node->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                    $node->setAttribute("xsi:schemaLocation", "http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd");

                    $node->setAttribute("identifier", "SelectPoint");
                    $node->setAttribute("title",$Question->getTitle());
                    $node->setAttribute("adaptive", "false");
                    $node->setAttribute("timeDependent", "false");
                    $document->appendChild($node);

                    // Add the tag <responseDeclaration> to <node>
                    $responseDeclaration = $document->CreateElement('responseDeclaration');
                    $responseDeclaration->setAttribute("identifier", "RESPONSE");
                    $responseDeclaration->setAttribute("cardinality", "single");
                    $responseDeclaration->setAttribute("baseType", "point");
                    $node->appendChild($responseDeclaration);

                    // add the tag <correctResponse> to the <responseDeclaration>
                    $correctResponse = $document->createElement("correctResponse");
                    $Tagvalue = $document->CreateElement("value");
                    $responsevalue =  $document->CreateTextNode($x_center_circle." ".$y_center_circle);
                    $Tagvalue->appendChild($responsevalue);
                    $correctResponse->appendChild($Tagvalue);
                    $responseDeclaration->appendChild($correctResponse);


                    //add <areaMapping> to <responseDeclaration>
                    $areaMapping = $document->createElement("areaMapping");
                    $areaMapping->setAttribute("defaultValue", "0");
                    $responseDeclaration->appendChild($areaMapping);

                    $areaMapEntry =  $document->createElement("areaMapEntry");
                    $areaMapEntry->setAttribute("shape", $coords[0]->getShape());
                    $areaMapEntry->setAttribute("coords",$x_center_circle.",".$y_center_circle.",".$radius);
                    $areaMapEntry->setAttribute("mappedValue", $coords[0]->getScoreCoords());
                    $areaMapping->appendChild($areaMapEntry);



                    //add tag <itemBody>... to <assessmentItem>
                    $itemBody =$document->createElement("itemBody");

                    $selectPointInteraction = $document->createElement("selectPointInteraction");
                    $selectPointInteraction->setAttribute("responseIdentifier", "RESPONSE");
                    $selectPointInteraction->setAttribute("maxChoices", "1");




                    $prompt = $document->CreateElement('prompt');
                    $prompttxt =  $document->CreateTextNode($interaction->getInvite());
                    $prompt->appendChild($prompttxt);
                    $selectPointInteraction->appendChild($prompt);

                    $object = $document->CreateElement('object');
                    $object->setAttribute("type","image/".$Documents[0]->getType());
                    $object->setAttribute("width",$interactionGraphic[0]->getWidth());
                    $object->setAttribute("height",$interactionGraphic[0]->getHeight());
                    $object->setAttribute("data",$Documents[0]->getUrl());
                    $objecttxt =  $document->CreateTextNode($Documents[0]->getLabel());
                    $object->appendChild($objecttxt);
                    $selectPointInteraction->appendChild($object);


                    $itemBody->appendChild($selectPointInteraction);
                    $node->appendChild($itemBody);
                    //save xml File
                    //comment
                    if(($interaction->getFeedBack()!=Null) && ($interaction->getFeedBack()!="") ){
                            $modalFeedback=$document->CreateElement('modalFeedback');
                            $modalFeedback->setAttribute("outcomeIdentifier","FEEDBACK");
                            $modalFeedback->setAttribute("identifier","COMMENT");
                            $modalFeedback->setAttribute("showHide","show");
                            $modalFeedbacktxt = $document->CreateTextNode($interaction->getFeedBack());
                            $modalFeedback->appendChild($modalFeedbacktxt);
                            $node->appendChild($modalFeedback);
                    }

                    $document->save($this->userDir.'testfile.xml');


                    /*search for the real path with the real name of the image)
                    */
                    $url = substr($Documents[0]->getUrl(), 1, strlen($Documents[0]->getUrl()));
                    $nom = explode("/", $url);

                    //generate tne mannifest file
                    $this->generate_imsmanifest_File($nom[count($nom)-1]);
                    //

                    $path=$_SERVER['DOCUMENT_ROOT'].$this->get('request')->getBasePath(). $url;
                    //create zip file and add the xml file with images...
                    $tmpFileName = tempnam($this->userDir.'tmp', "xb_");
                    $zip = new \ZipArchive();
                    $zip->open($tmpFileName, \ZipArchive::CREATE);
                    $zip->addFile($this->userDir.'testfile.xml', 'SchemaQTI.xml');
                    $zip->addFile($this->userDir.'imsmanifest.xml', 'imsmanifest.xml');
                    if(!empty($path)){
                            $zip->addFile($path, "images/".$nom[count($nom)-1]);
                    }
                    $zip->close();
                    $response = new BinaryFileResponse($tmpFileName);
                    //$response->headers->set('Content-Type', $content->getContentType());
                    $response->headers->set('Content-Type', 'application/application/zip');
                    $response->headers->set('Content-Disposition', "attachment; filename=QTIarchive.zip");


                    return $response;





                case "InteractionHole":

                         $interactionHole = $this->doctrine
                                                    ->getManager()
                                                    ->getRepository('UJMExoBundle:InteractionHole')->findBy(array('interaction' => $interaction->getId()));

                         $ujmHole = $this->doctrine
                                                    ->getManager()
                                                    ->getRepository('UJMExoBundle:Hole')->findBy(array('interactionHole' => $interactionHole[0]->getId()));
                         $ujm_word_response = $this->doctrine
                                                 ->getManager()
                                                 ->getRepository('UJMExoBundle:WordResponse')->findAll(array('hole' => $ujmHole));




                    //creation of the XML FIle
                     $document = new \DOMDocument();

                   // on crée l'élément principal <Node>
                    $node = $document->CreateElement('assessmentItem');
                    $node->setAttribute("xmlns", "http://www.imsglobal.org/xsd/imsqti_v2p1");
                    $node->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                    $node->setAttribute("xsi:schemaLocation", "http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd");

                    $node->setAttribute("identifier", "textEntry");
                    $node->setAttribute("title",$Question->getTitle());
                    $node->setAttribute("adaptive", "false");
                    $node->setAttribute("timeDependent", "false");
                    $document->appendChild($node);

                    // Add the tag <responseDeclaration> to <node>
                    $responseDeclaration = $document->CreateElement('responseDeclaration');
                    $responseDeclaration->setAttribute("identifier", "RESPONSE");
                    $responseDeclaration->setAttribute("cardinality", "single");
                    $responseDeclaration->setAttribute("baseType", "string");
                    $node->appendChild($responseDeclaration);

                    //add <mapping> to <responseDeclaration>
                    //add <mapEntry> to <responseDeclaration>
                    $mapping = $document->createElement("mapping");
                    $mapping->setAttribute("defaultValue", "0");



                    // add the tag <correctResponse> to the <responseDeclaration>
                    $correctResponse = $document->createElement("correctResponse");

                    foreach($ujm_word_response as $resp){


                        $Tagvalue = $document->CreateElement("value");
                        $responsevalue =  $document->CreateTextNode($resp->getResponse());
                        $Tagvalue->appendChild($responsevalue);
                        $correctResponse->appendChild($Tagvalue);
                        $responseDeclaration->appendChild($correctResponse);


                        //response .... mapentry
                         $mapEntry =  $document->createElement("mapEntry");
                         $mapEntry->setAttribute("mapKey", $resp->getResponse());
                         $mapEntry->setAttribute("mappedValue",$resp->getScore());
                         $mapping->appendChild($mapEntry);

                    }

                    $responseDeclaration->appendChild($mapping);

                    $outcomeDeclaration = $document->createElement("outcomeDeclaration");
                    $outcomeDeclaration->setAttribute("identifier", "SCORE");
                    $outcomeDeclaration->setAttribute("cardinality", "single");
                    $outcomeDeclaration->setAttribute("baseType", "float");
                    $node->appendChild($outcomeDeclaration);

                    //add tag <itemBody>... to <assessmentItem>
                    $itemBody = $document->createElement("itemBody");
                            //change the tag <input....> by <inputentry.....>
                           $qst = $interactionHole[0]->getHtmlWithoutValue();
                           $regex = '(<input\\s+id="\d+"\\s+class="blank"\\s+name="blank_\d+"\\s+size="\d+"\\s+type="text"\\s+value=""\\s+\/>)';
                           $result = preg_replace($regex, '<textEntryInteraction responseIdentifier="RESPONSE" expectedLength="15"/>', $qst);
                    $objecttxt =  $document->CreateTextNode($result);
                    $itemBody->appendChild($objecttxt);


                    $node->appendChild($itemBody);

                    //comment
                    if(($interaction->getFeedBack()!=Null) && ($interaction->getFeedBack()!="") ){
                            $modalFeedback=$document->CreateElement('modalFeedback');
                            $modalFeedback->setAttribute("outcomeIdentifier","FEEDBACK");
                            $modalFeedback->setAttribute("identifier","COMMENT");
                            $modalFeedback->setAttribute("showHide","show");
                            $modalFeedbacktxt = $document->CreateTextNode($interaction->getFeedBack());
                            $modalFeedback->appendChild($modalFeedbacktxt);
                            $node->appendChild($modalFeedback);
                    }


                    //save xml File
                    $document->save($this->userDir.'Q_Hole.xml');


                    //create zip file and add the xml file with images...
                    $tmpFileName = tempnam($this->userDir.'tmp', "xb_");
                    $zip = new \ZipArchive();
                    $zip->open($tmpFileName, \ZipArchive::CREATE);
                    $zip->addFile($this->userDir.'Q_Hole.xml', 'QTI-Q-HoleShema.xml');

                    $zip->close();
                    $response = new BinaryFileResponse($tmpFileName);
                    //$response->headers->set('Content-Type', $content->getContentType());
                    $response->headers->set('Content-Type', 'application/application/zip');
                    $response->headers->set('Content-Disposition', "attachment; filename=QTI-archive-Q-Hole.zip");


                    return $response;

                case "InteractionOpen":

                                $interactionOpen = $this->doctrine->getManager()
                                                        ->getRepository('UJMExoBundle:InteractionOpen')->getInteractionOpen($interaction->getId());







                                //creation of the XML FIle
                                 $document = new \DOMDocument();

                                // on crée l'élément principal <Node>
                                $node = $document->CreateElement('assessmentItem');
                                $node->setAttribute("xmlns", "http://www.imsglobal.org/xsd/imsqti_v2p1");
                                $node->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                                $node->setAttribute("xsi:schemaLocation", "http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd");

                                $node->setAttribute("identifier", "extendedText");
                                $node->setAttribute("title",$Question->getTitle());
                                $node->setAttribute("adaptive", "false");
                                $node->setAttribute("timeDependent", "false");
                                $document->appendChild($node);

                                // Add the tag <responseDeclaration> to <node>
                                $responseDeclaration = $document->CreateElement('responseDeclaration');
                                $responseDeclaration->setAttribute("identifier", "RESPONSE");
                                $responseDeclaration->setAttribute("cardinality", "single");
                                $responseDeclaration->setAttribute("baseType", "string");
                                $node->appendChild($responseDeclaration);

                                //add <mapping> to <responseDeclaration>
                                //add <mapEntry> to <responseDeclaration>
                                $outcomeDeclaration = $document->createElement("outcomeDeclaration");
                                $outcomeDeclaration->setAttribute("identifier", "Score");
                                $outcomeDeclaration->setAttribute("cardinality", "single");
                                $outcomeDeclaration->setAttribute("baseType", "float");

                                // add the tag <correctResponse> to the <responseDeclaration>
                                $defaultValue = $document->createElement("defaultValue");




                                $Tagvalue = $document->CreateElement("value");
                                $responsevalue =  $document->CreateTextNode($interactionOpen[0]->getScoreMaxLongResp());
                                $Tagvalue->appendChild($responsevalue);
                                $defaultValue->appendChild($Tagvalue);
                                $outcomeDeclaration->appendChild($defaultValue);


                                $node->appendChild($outcomeDeclaration);

                                //add tag <itemBody>... to <assessmentItem>
                                $itemBody = $document->createElement("itemBody");


                                $objecttxt =  $document->CreateTextNode($interaction->getInvite());
                                $itemBody->appendChild($objecttxt);


                                $node->appendChild($itemBody);

                                //comment
                                if(($interaction->getFeedBack()!=Null) && ($interaction->getFeedBack()!="") ){
                                        $modalFeedback=$document->CreateElement('modalFeedback');
                                        $modalFeedback->setAttribute("outcomeIdentifier","FEEDBACK");
                                        $modalFeedback->setAttribute("identifier","COMMENT");
                                        $modalFeedback->setAttribute("showHide","show");
                                        $modalFeedbacktxt = $document->CreateTextNode($interaction->getFeedBack());
                                        $modalFeedback->appendChild($modalFeedbacktxt);
                                        $node->appendChild($modalFeedback);
                                }


                                //save xml File
                                $document->save($this->userDir.'Q_Open.xml');


                                //create zip file and add the xml file with images...
                                $tmpFileName = tempnam($this->userDir.'tmp', "xb_");
                                $zip = new \ZipArchive();
                                $zip->open($tmpFileName, \ZipArchive::CREATE);
                                $zip->addFile($this->userDir.'Q_Open.xml', 'QTI-Q-OpenShema.xml');

                                $zip->close();
                                $response = new BinaryFileResponse($tmpFileName);
                                //$response->headers->set('Content-Type', $content->getContentType());
                                $response->headers->set('Content-Type', 'application/application/zip');
                                $response->headers->set('Content-Disposition', "attachment; filename=QTI-archive-Q-Open.zip");


                                return $response;



            }
    }

    private function createDirQTI()
    {
        if (!is_dir('./uploads/ujmexo/')) {
            mkdir('./uploads/ujmexo/');
        }
        if (!is_dir('./uploads/ujmexo/qti/')) {
            mkdir('./uploads/ujmexo/qti/');
        }
        if (!is_dir($this->userDir)) {
            mkdir($this->userDir);
        }
    }

    Private function generate_imsmanifest_File($namefile)
    {

                    $document = new \DOMDocument();
                    // on crée l'élément principal <Node>
                    $node = $document->CreateElement('manifest');
                    $node->setAttribute("xmlns", "http://www.imsglobal.org/xsd/imscp_v1p1");
                    $node->setAttribute("xmlns:imsmd", "http://www.imsglobal.org/xsd/imsmd_v1p2");
                    $node->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                    $node->setAttribute("xmlns:imsqti", "http://www.imsglobal.org/xsd/imsqti_metadata_v2p1");
                    $node->setAttribute("xsi:schemaLocation", "http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd http://www.imsglobal.org/xsd/imsmd_v1p2 imsmd_v1p2p4.xsd http://www.imsglobal.org/xsd/imsqti_metadata_v2p1  http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_metadata_v2p1.xsd");

                    $document->appendChild($node);
                    // Add the tag <responseDeclaration> to <node>
                    $metadata = $document->CreateElement('metadata');
                    $node->appendChild($metadata);

                    $schema = $document->CreateElement('schema');
                    $schematxt = $document->CreateTextNode('IMS Content');
                    $schema->appendChild($schematxt);
                    $metadata->appendChild($schema);


                    $schemaversion=$document->CreateElement('schemaversion');
                    $schemaversiontxt = $document->CreateTextNode('1.1');
                    $schemaversion->appendChild($schemaversiontxt);
                    $metadata->appendChild($schemaversion);

                    $resources = $document->CreateElement('resources');
                    $node->appendChild($resources);

                    $resource = $document->CreateElement('resource');
                    $resource->setAttribute("type","imsqti_item_xmlv2p1");
                    //the name of the file must be variable ....
                    $resource->setAttribute("href","SchemaQTI.xml");
                    $resources->appendChild($resource);

                    $file = $document->CreateElement('file');
                    $file->setAttribute("href","SchemaQTI.xml");
                    $resource->appendChild($file);

                    $file2 = $document->CreateElement('file');
                    //the name of the image must be variable ....
                    $file2->setAttribute("href","images/".$namefile);
                    $resource->appendChild($file2);

                    $document->save($this->userDir.'imsmanifest.xml');




    }
    
    /**
     * Generate head of QTI
     * 
     * @access private
     *
     * @param \DOMDocument $document
     * @param String $identifier type question
     * @param String $title title of question
     * 
     */
    private function qtiHead($document, $identifier, $title)
    {
        $this->node = $document->CreateElement('assessmentItem');
        $this->node->setAttribute("xmlns", "http://www.imsglobal.org/xsd/imsqti_v2p1");
        $this->node->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $this->node->setAttribute("xsi:schemaLocation", "http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd");
        $this->node->setAttribute("identifier", $identifier);
        $this->node->setAttribute("title", $title);
        $this->node->setAttribute("adaptive", "false");
        $this->node->setAttribute("timeDependent", "false");
        $document->appendChild($this->node);
    }
    
    /**
     * Add the tag responseDeclaration to node
     * 
     * @access private
     *
     * @param \DOMDocument $document
     * @param String $baseType
     * 
     */
    private function qtiResponseDeclaration($document, $baseType)
    {
        $responseDeclaration = $document->CreateElement('responseDeclaration');
        $responseDeclaration->setAttribute("identifier", "RESPONSE");
        $responseDeclaration->setAttribute("cardinality", "single");
        $responseDeclaration->setAttribute("baseType", $baseType);
        $this->node->appendChild($responseDeclaration);

        return $responseDeclaration;
    }
    
    /**
     * add the tag outcomeDeclaration to the node
     * 
     * @access private
     *
     * @param \DOMDocument $document
     * 
     */
    private function qtiOutComeDeclaration($document)
    {
        $outcomeDeclaration = $document->CreateElement('outcomeDeclaration');
        $outcomeDeclaration->setAttribute("identifier", "SCORE");
        $outcomeDeclaration->setAttribute("cardinality", "single");
        $outcomeDeclaration->setAttribute("baseType", "float");
        $this->node->appendChild($outcomeDeclaration);
        
        return $outcomeDeclaration;
    }
    
    /**
     * add the tag modalFeedback to the node
     * 
     * @access private
     *
     * @param \DOMDocument $document
     * @param String $feedBack
     * 
     */
    private function qtiFeedBack ($document, $feedBack)
    {
        $modalFeedback=$document->CreateElement('modalFeedback');
        $modalFeedback->setAttribute("outcomeIdentifier","FEEDBACK");
        $modalFeedback->setAttribute("identifier","COMMENT");
        $modalFeedback->setAttribute("showHide","show");
        $modalFeedbacktxt = $document->CreateTextNode($feedBack);
        $modalFeedback->appendChild($modalFeedbacktxt);
        $this->node->appendChild($modalFeedback);
    }
}