# TagBundle


########################
# HOW TO TAG AN ENTITY #
########################

To tag an Object, a "Claroline\CoreBundle\Event\GenericDataEvent" event has to be dispatched.
Event name must be "claroline_tag_object".
Event "datas" field must be an array defined as followed :

array (
    'tag' => [String],        // Name of the tag
    'object' => [Any entity], // Object that has to be tagged
    'user' => [User]          // Optional. Owner of the tag. NULL by default.
)

Here is an example of a call from a controller function to tag a workspace with "My Tags" :

    *******************************************************************
        $data = array('tag' => 'My Tags', 'object' => $workspace);

        $this->get('claroline.event.event_dispatcher')->dispatch(
            'claroline_tag_object',
            'GenericData',
            array($data)
        );
    *******************************************************************

    OR

    *******************************************************************
        $data = array('tag' => 'My Tags', 'object' => $workspace);
        $event = new GenericDataEvent();
        $event->setData($data);

        $this->get('event_dispatcher')->dispatch(
            'claroline_tag_object',
            $event
        );
    *******************************************************************


###############################
# HOW TO FETCH TAGGED OBJECTS #
###############################

To fetch tagged objects, a "Claroline\CoreBundle\Event\GenericDataEvent" event has also to be dispatched.
Event name must be "claroline_retrieve_tagged_objects".
Event "datas" field must be an array defined as followed :

array (
    'tag' => [String],             // Name of the tag
    'user' => [User]               // Optional. Owner of the tag. NULL by default.
    'with_platform' => [Boolean]   // Optional. Define if platform tags have to be considered too when user option is defined. False by default.
    'strict' => [Boolean]          // Optional. Define if the tag option has to be completely or partially matched. False by default.
    'class' => [String]            // Optional. Class of the desired fetched objects. If used, only objects of that class will be fetched. NULL by default.
    'object_response' => [Boolean] // Optional. Define if returned values are casted to class option. If not, it is simply an array of values. False by default. 'class' option is required.
    'ordered_by' => [String]       // Optional. Field to order. Define order of casted returned objects. 'id' by default. 'class' option is required.
    'order' => [String]            // Optional. Order. Define order of casted returned objects. 'ASC' by default. 'class' option is required.
    'ids' => [Array]               // Optional. List of ids. Only objects that have their id in the list will be fetched.
)

Here is an example to fetch all workspaces tagged as "My Tags", ordered by name :

    **************************************************************************
        $data = array(
            'tag' => 'My Tags',
            'strict' => true,
            'class' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
            'object_response' => true,
            'ordered_by' => 'name',
            'order' => 'ASC'
        );

        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            'claroline_retrieve_tagged_objects',
            'GenericData',
            array($data)
        );

        $workspaces = $event->getResponse();
    **************************************************************************

If 'class' option is not defined or 'object_response' is not true, the response value is an array defined as followed :

array(
    array(
        'class' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
        'id' => 45,
        'name' => 'Workspace ABC'
    ),
    array(
        'class' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
        'id' => 74,
        'name' => 'Workspace XYZ'
    ),
    array(
        'class' => 'Claroline\CoreBundle\Entity\User',
        'id' => 11,
        'name' => 'John DOE'
    ),
    ...
)
