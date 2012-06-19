//todo: l'event close de la boite de dialogue
$(function(){
    resourceTypeArray = new Array();
    subItems = {};
    idUserRepository = "";
    getUserRepositoryId();

    var modal = createTreeDialog();

    $('#dialog_tree_button').click(function(){
        modal.modal("show");
    });

    $.ajax({
        type: 'POST',
        url: Routing.generate('claro_resource_type_resource', {
            'format':'json', 'listable':'true'
        }),
        success: function(data){
            //JSON.parse doesn't work: why ?
            var JSONObject = eval(data);
            var cpt = 0;

            while (cpt<JSONObject.length) {
                resourceTypeArray[cpt]=JSONObject[cpt];
                cpt++;
            }
            subItems = generateSubItems();
        },
        error: function(data){
            alert("resource type loading failed");
        }
    });


    $('#workspace_source_list_button').click(function(){
        appendRegisteredWorkspacesList('#source_tree');
    });

    $('#workspace_destination_list_button').click(function(){
        appendRegisteredWorkspacesList('#destination_tree');
    });

    $('.cfp_workspace_show_tree').live("click", function (event){
        var divId = event.target.parentElement.attributes[0].nodeValue;
        $('#'+divId).empty();
        var repositoryId = event.target.attributes[0].value;
        $('#'+divId).dynatree("destroy");
        createTree('#'+divId, repositoryId);
    });

    //créé après la récupération de resourceType~ à changer
    function createTree(treeId, repositoryId)
    {
        $(treeId).dynatree({
            title: "myTree",
            initAjax:{
                url:Routing.generate('claro_resource_node',{
                    'instanceId':0,
                    'workspaceId': repositoryId,
                    'format':'json'
                })
                },
            clickFolderMode: 1,
            onLazyRead: function(node){
                node.appendAjax({
                    url:Routing.generate('claro_resource_node', {
                        'instanceId':node.data.key,
                        'workspaceId': repositoryId,
                        'format': 'json'
                    })
                });
            },
            onCreate: function(node, span){
                bindContextMenu(node, repositoryId);
            },
            onDblClick: function(node)
            {
                node.expand();
                node.activate();
            },
            onCustomRender: function(node){
                var html = "<a class='dynatree-title' style='cursor:pointer;' href='#'> "+node.data.title+" </a>";
                html += "<span class='dynatree-custom-claro-menu' id='dynatree-custom-claro-menu-"+node.data.key+"' style='cursor:pointer; color:blue;'> menu </span>";
                return html;
            },
            dnd: {
                onDragStart: function(node){
                    return true;
                },
                onDragStop: function(node){
                },
                autoExpandMS: 1000,
                preventVoidMoves: true,

                onDragEnter: function(node, sourceNode){
                    return true;
                },
                onDragOver: function(node, sourceNode, hitMode){
                    if(node.isDescendantOf(sourceNode)){
                        return false;
                    }
                },
                onDrop: function(node, sourceNode, hitMode, ui, draggable){
                    /**This function MUST be defined to enable dropping of items on the tree.
                     * sourceNode may be null, if it is a non-Dynatree droppable.
                     */
                    logMsg("tree.onDrop(%o, %o)", node, sourceNode);
                    var copynode;
                    if(sourceNode) {
                        copynode = sourceNode.toDict(true, function(dict){
                            dict.title = "Copy of " + dict.title;
                            delete dict.key; // Remove key, so a new one will be created
                        });
                    }else{
                        copynode = {
                            title: "This node was dropped here (" + ui.helper + ")."
                        };
                    }
                    if(hitMode == "over"){
                        // Append as child node
                        node.addChild(copynode);
                        // expand the drop target
                        node.expand(true);
                    }else if(hitMode == "before"){
                        // Add before this, i.e. as child of current parent
                        node.parent.addChild(copynode, node);
                    }else if(hitMode == "after"){
                        // Add after this, i.e. as child of current parent
                        node.parent.addChild(copynode, node.getNextSibling());
                    }
                },
                onDragLeave: function(node, sourceNode){
                }
            }
        });
    }

    function sendRequest(route, routeParams, successHandler){
        $.ajax({
            type: 'POST',
            url: Routing.generate(route, routeParams),
            cache: false,
            success: successHandler,
            error: function(xhr){
                alert(xhr.status);
            }
        });
    }

    function submissionHandler(data, route, routeParameters)
    {
        try{
            var JSONObject = JSON.parse(data);
            var node = $("#ct_tree").dynatree("getTree").selectKey(routeParameters.instanceParentId);
            if(JSONObject.type != 'directory') {
                var childNode = node.addChild({
                    title:JSONObject.name,
                    key:JSONObject.key
                });
            }
            else
            {
                var childNode = node.addChild({
                    title:JSONObject.name,
                    key:JSONObject.key,
                    isFolder:true
                });
            }

            $('#ct_form').empty();
        }
        catch(err)
        {
            $('#ct_form').empty();
            $('#ct_form').append(data);
            $("#generic_form").submit(function(e){
                e.preventDefault();
                sendForm(route, routeParameters, document.getElementById("generic_form"));
            });
        }
    }

    function successHandler()
    {
        alert("success");
    }

    function createFormDialog(type, id)
    {
        console.debug(type);
        var route = Routing.generate('claro_resource_form', {
            'type':type,
            'instanceParentId':id
        });
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#ct_form').empty();
                $('#ct_form').append(data);
                $("#generic_form").submit(function(e){
                    e.preventDefault();
                    sendForm("claro_resource_create",  {
                        'type':type,
                        'id':id
                    }, document.getElementById("generic_form"));
                });
            }
        });
    }

    function deleteNode(node)
    {
        var repoId = document.getElementById('data-claroline').getAttribute('data-workspace_id');
        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_resource_remove_workspace',{
                'resourceId':node.data.key,
                'workspaceId':repoId
            }),

            success: function(data){
                if(data=="success")
                {
                    node.remove();
                }
            }
        });
    }

    function openNode(node, repositoryId)
    {
        window.location = Routing.generate('claro_resource_open',{
            'workspaceId': repositoryId,
            'instanceId':node.data.key
        });
    }

    function viewNode(node)
    {
        window.location = Routing.generate('claro_resource_default_click',{
            'instanceId':node.data.key
        });
    }

    function bindContextMenu(node, repositoryId){

        var menuDefaultOptions = {
            selector: 'a.dynatree-title',
            callback: function(key, options) {
                switch(key)
                {
                    case "open":
                        openNode(node, repositoryId);
                        break;
                    case "delete":
                        deleteNode(node);
                        break;

                    case "view":
                        viewNode(node, key);
                        break;
                    default:
                        node = $.ui.dynatree.getNode(this);
                        createFormDialog(key, node.data.key);
                }
            },
            items: {
                "new": {
                    name: "new",
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        if(node.data.isFolder == true)
                        {
                            return false;
                        }
                        else
                        {
                            return true;
                        }
                    },
                    items:subItems
                },
                "open": {
                    name: "open",
                    accesskey:"o",
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        if(node.data.isFolder != true)
                        {
                            return false;
                        }
                        else
                        {
                            return true;
                        }
                    }
                },
                "view": {
                    name: "view",
                    accesskey:"v"
                },
                "delete": {
                    name: "delete",
                    accesskey:"d",
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        if(node.data.key != 0)
                        {
                            return false;
                        }
                        else
                        {
                            return true;
                        }
                    }
                }
            }
        }

        $.contextMenu(menuDefaultOptions);

        var additionalMenuOptions = $.extend(menuDefaultOptions,
        {
            selector: 'span.dynatree-custom-claro-menu',
            trigger: 'left'
        });

        $.contextMenu(additionalMenuOptions);
    }

    function sendForm(route, routeParameters, form)
    {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', Routing.generate(route, routeParameters), true);
        xhr.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
        xhr.onload = function(e){
            submissionHandler(xhr.responseText, route, routeParameters)
            };
        xhr.send(formData);
    }

    function generateSubItems()
    {
        var cpt = 0;
        var subItems='';
        subItems+='{'
        while (cpt<resourceTypeArray.length)
        {
            var name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            subItems+= '"'+resourceTypeArray[cpt].type+'": {"name":"'+translation+'"}';
            cpt++;
            if (cpt<resourceTypeArray.length) {
                subItems+=",";
            }
        }
        subItems+='}'
        console.debug(subItems);
        object = JSON.parse(subItems);
        console.debug(object);
        return object;
    }

    function createTreeDialog()
    {
        console.debug("MOI PASSER");
        document.getElementById('ct_dialog').setAttribute("class", 'modal fade');
        var modalContent = ""
        +'<div class="modal-header">'
        +'<button id="close_dialog_button" class="close" data-dismiss="modal">×</button>'
        +'<h3> header</h3>'
        +'</div>'
        +'<div class="modal-body">'
        +"<div id='ct_form'><table id='ct_tree'><thead><tr><th><button id='workspace_source_list_button'>workspace source list</button></th>"
        +"<th><button id='workspace_destination_list_button'>workspace destination list</button></th></th></thead><tbody><tr valign='top'>"
        +"<td><div id='source_tree'></div></td><td><div id='destination_tree'></div></td></tr></tbody></table>"
        +'</div>'
        +'<div class="modal-footer">'
        +'FOOTER'
        +'</div>';

        $('#ct_dialog').append(modalContent);

        var modal = $('#ct_dialog').modal({
            show: false,
            backdrop: false
        });

        return modal;
    }

    function setSubItemsTranslations()
    {
        var cpt = 0;
        var name = "";
        while(cpt < resourceTypeArray.length) {
            name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            console.debug(translation);
            resourceTypeArray[cpt].type=translation;
            cpt++;
        }
    }

    function getUserRepositoryId()
    {
        $.ajax({
            type: 'POST',
            url: Routing.generate("claro_ws_user_workspace_id"),
            cache: false,
            success: function(data) {
                idUserRepository = data;
            },
            error: function(xhr) {
                alert(xhr.status);
            }
        });
    }

    function appendRegisteredWorkspacesList(tableSelector)
    {
        $(tableSelector).empty();

        var html="WORKSPACES : <br>";
        html += "<a class='cfp_workspace_show_tree' href='#' data-workspace_id="+idUserRepository+">"
        html += "local"
        html += "</a></br>";

        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_ws_list_user_workspaces', {
                format:'json'
            }),
            cache: false,
            success: function(data){
                JSONObject = JSON.parse(data);
                var cpt = 0;

                while (cpt<JSONObject.length) {
                    var name = JSONObject[cpt].name;
                    var id = JSONObject[cpt].id;
                    html +="<a class='cfp_workspace_show_tree' href='#' data-workspace_id="+id+">"
                    html += name
                    html +="</a></br>";
                    cpt++;
                }

                $(tableSelector).append(html);
            },
            error: function(xhr){
                alert(xhr.status);
            }
        });
    }
});
