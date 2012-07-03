$(function(){
    resourceTypeArray = new Array();
    subItems = {};
    copiedNode = null;
    $.ajax({
        type: 'POST',
        url: Routing.generate('claro_resource_type_resource', {'format':'json', 'listable':'true'}),
        success: function(data){
            var JSONObject = eval(data);
            var cpt = 0;
            while (cpt<JSONObject.length) {
                resourceTypeArray[cpt]=JSONObject[cpt];
                cpt++;
            }
        },
        error: function(data){
            alert("resource type loading failed");
        },
        complete: function(jqXHR, textStatus)
        {
            //setSubItemsTranslations();
            subItems = generateSubItems();
            createDivTree();
            createTree('#source_tree');
        }
    });

    function createTree(treeId)
    {
        $(treeId).dynatree({
            title: 'myTree',
            initAjax:{
                url:Routing.generate('claro_resource_root_node',{
                    'format':'json'
                })
            },
            clickFolderMode: 1,
            onLazyRead: function(node){
                node.appendAjax({
                    url:Routing.generate('claro_resource_node', {
                        'instanceId':node.data.key,
                        'workspaceId':node.data.workspaceId,
                        'format': 'json'
                    })
                });
            },
            onCreate: function(node, span){
                bindContextMenuTree(node);
            },
            onDblClick: function(node)
            {
                node.expand();
                node.activate();
            },
            onCustomRender: function(node){
                var html = "<a class='dynatree-title' style='cursor:pointer;' href='#'> "+node.data.title+" share "+node.data.shareType+" </a>";
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
                    if (node.isDescendantOf(sourceNode)){
                        return false;
                    }
                    else {
                        sendRequest('claro_resource_move', {'idChild': sourceNode.data.key, 'idParent': node.data.key, 'workspaceDestinationId':node.data.workspaceId});
                        sourceNode.move(node, hitMode);
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

    function submissionHandler(xhr, route, routeParameters, node)
    {
        if(xhr.getResponseHeader('Content-Type') == 'application/json')
        {
            var JSONObject = JSON.parse(xhr.responseText);
            var instance = JSONObject[0];
            var newNode = {
                    title:instance.title,
                    key:instance.key,
                    copy:instance.copy,
                    instanceCount:instance.instanceCount,
                    shareType:instance.shareType,
                    resourceId:instance.resourceId
                }

            if (instance.type == 'directory')
            {
                newNode.isFolder = true;
            }

            if(node.data.key != newNode.key)
            {
                node.appendAjax({url:Routing.generate('claro_resource_node', {
                    'instanceId':node.data.key,
                    'workspaceId': document.getElementById(node.tree.divTree.attributes[0].value).getAttribute('data-workspaceId'),
                    'format': 'json'})
                });
                node.expand();
            }
            else
            {
                node.data.title = newNode.title;
                node.data.shareType = newNode.shareType;
                node.render();
            }

            $('#ct_tree').show();
            $('#ct_form').empty();
        }
        else
        {
            $('#ct_form').append(xhr.responseText).empty();
            $('#generic_form').submit(function(e){
                e.preventDefault();
                sendForm(route, routeParameters, document.getElementById('generic_form'), node);
            });
            $('#resource_options_form').submit(function(e){
                e.preventDefault();
                sendForm('claro_resource_edit_options',  {'instanceId': node.data.key}, document.getElementById('resource_options_form'), node);
            });
        }
    }

    function successHandler()
    {
        alert('success');
    }

    function createFormDialog(type, id, node)
    {
        var route = Routing.generate('claro_resource_form', {'type':type,'instanceParentId':id});
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#ct_form').append(data);
                $('#generic_form').submit(function(e){
                    e.preventDefault();
                    sendForm('claro_resource_create',
                        {'type':type,'instanceParentId':id, 'workspaceId': node.data.workspaceId},
                        document.getElementById('generic_form'), node
                    );
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
                if(data == 'success'){
                    node.remove();
                }
            }
        });
    }

    function copyNode(node)
    {
        copiedNode = node;
    }

    function pasteNode(node)
    {
        if(null == copiedNode){
            alert("can't paste the void");
        }

        var html = getMoveFormHtml();
        $('#ct_form').append(html);
        $('#move_resource_form_submit').click(function(e) {
            e.preventDefault();
            var option = getCheckedValue(document.forms['move_resource_form']['options']);
            sendRequest('claro_resource_add_workspace',
                        {'instanceId':copiedNode.data.key,'instanceDestinationId':node.data.key,'options':option,'workspaceId':node.data.workspaceId}
            );

            var newNode = {
                    title:copiedNode.data.title,
                    key:copiedNode.data.key,
                    copy:copiedNode.data.copy,
                    instanceCount:copiedNode.data.instanceCount,
                    shareType:copiedNode.data.shareType,
                    resourceId:copiedNode.data.resourceId
                }

            node.addChild(newNode);
            $('#ct_form').empty();
            copiedNode = null;
        });

    }

    function openNode(node)
    {
        window.location = Routing.generate('claro_resource_open',{'instanceId':node.data.key});
    }

    function viewNode(node)
    {
        window.location = Routing.generate('claro_resource_default_click',{'instanceId':node.data.key});
    }

    function optionsNode(node)
    {
        var route = Routing.generate('claro_resource_options_form', {
            instanceId: node.data.key
        });
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#ct_tree').hide();
                $('#ct_form').append(data);
                $("#resource_options_form").submit(function(e){
                    e.preventDefault();
                    sendForm('claro_resource_edit_options',  {'instanceId': node.data.key}, document.getElementById('resource_options_form'), node);
                });
            }
        });
    }

    function workspaceRightNode(node)
    {
        //window.location = Routing.generate('claro_ws_properties',{'workspaceId':node.data.workspaceId});
        alert("clock");
        $('#workspace_settings').modal('show');
        alert('clack');
    }

    function createWorkspaceSettingsPopup()
    {

    }

    function bindContextMenuTree(node){
        isWorkspace='';
        (node.data.key == 0) ? isWorkspace = true : isWorkspace = false;
        var menuDefaultOptions = {
            selector: 'a.dynatree-title',
            callback: function(key, options) {
                switch(key)
                {
                    case 'open': openNode(node); break;
                    case 'delete':deleteNode(node); break;
                    case 'options': optionsNode(node); break;
                    case 'workspace_properties': workspaceRightNode(node); break;
                    case 'copy': copyNode(node); break;
                    case 'paste': pasteNode(node); break;
                    case 'cut': cutNode(node); break;
                    default:node = $.ui.dynatree.getNode(this);
                        createFormDialog(key, node.data.key, node);break;
                }
            },
            items: {
                'new': {
                    name: 'new',
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        return (node.data.isFolder) ? false : true;
                    },
                    items:subItems
                },
                'open': {
                    name: 'open', accesskey:"o",
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        return (node.data.isFolder)? false : true;
                    }
                },
                'view': {name: 'view', accesskey:'v'},
                'delete': {
                    name: 'delete', accesskey:'d',
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        return (node.data.key != 0)? false: true;
                    }
                },
                'resource_properties': {
                    name: 'properties',
                    items: {
                        'options' : {name: 'options'},
                        'rights' : { name :'rights'}
                    }
                },
                'workspace_properties':{name: 'workspace properties'},
                'copy': {name: "copy"},
                'paste': { name: 'paste',
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        return (node.data.isFolder)? false : true;
                    }
                }
            }
        }

        $.contextMenu(menuDefaultOptions);
        var additionalMenuOptions = $.extend(
            menuDefaultOptions,
            {selector: 'span.dynatree-custom-claro-menu', trigger: 'left'}
        );

        $.contextMenu(additionalMenuOptions);
    }

    function sendForm(route, routeParameters, form, node)
    {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', Routing.generate(route, routeParameters), true);
        xhr.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
        xhr.onload = function(e){
            submissionHandler(xhr, route, routeParameters, node);
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
        var object = JSON.parse(subItems);

        return object;
    }

    function createDivTree()
    {
        var content = ""
        +"<div id='workspace_settings' class='modal fade'></div>"
        +"<div id='ct_form'></div><br>"
        +"<div id='source_tree'></div>"
        $('#ct_dialog').append(content)
        var modalContent = Twig.render(bootstrap_modal);
        $('#workspace_settings').append(modalContent).modal({show:true,backdrop:true});
    }

    function setSubItemsTranslations()
    {
        var cpt = 0;
        var name = "";
        while(cpt < resourceTypeArray.length) {
            name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            resourceTypeArray[cpt].type=translation;
            cpt++;
        }
    }

    function getMoveFormHtml()
    {
        var html = twig.Render(move_resource_form);
        return html;
    }

    function getCheckedValue(radioObj) {
        if(!radioObj)
            return "";
        var radioLength = radioObj.length;
        if(radioLength == undefined)
            if(radioObj.checked)
                return radioObj.value;
            else
                return "";
        for(var i = 0; i < radioLength; i++) {
            if(radioObj[i].checked) {
                return radioObj[i].value;
            }
        }
        return "";
    }

});
