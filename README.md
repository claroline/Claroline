# TagBundle


########################
# HOW TO TAG AN ENTITY #
########################

To tag an Object, a "Claroline\CoreBundle\Event\GenericDatasEvent" event has to be dispatched.
Event name must be "claroline_tag_item".
Event "datas" field must be an array defined as followed :

array (
    'tag' => <Name of the tag>,                 // String
    'item' => <Object that has to be tagged>,   // Any entity
    'user' => <Owner of the tag>                // Optional. User entity. NULL by default.
)

Here is an example of a call from a controller function to tag a workspace with "My Tags" :

*****************************************************************
    $datas = array("tag" => "My Tags", "item" => $workspace);

    $this->get("claroline.event.event_dispatcher")->dispatch(
        "claroline_tag_item",
        "GenericDatas",
        array($datas)
    );
*****************************************************************

OR

*****************************************************************
    $datas = array("tag" => "My Tags", "item" => $workspace);
    $event = new GenericDatasEvent();
    $event->setDatas($datas);

    $this->get("event_dispatcher")->dispatch(
        "claroline_tag_item",
        $event
    );
*****************************************************************