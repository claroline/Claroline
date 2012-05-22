     
    //var route = Routing.generate('claro_resource_JSON_node',{'id':0});  
    //todo: l'event close de la boite de dialogue
    
    //variable globales = pas bon
    resourceTypeArray = new Array();
    subItems = {};
    getUserRepositoryId();
    idUserRepository = "";
    
    $(function(){
        
        var modal = createTreeDialog();
             
        $('#dialog_tree_button').click(function(){
            modal.modal("show");
        });
     
        $.ajax({
        type: 'POST',
        url: Routing.generate('claro_resource_type_resource'),
        success: function(data){
                //JSON.parse doesn't work: why ?
                var JSONObject = eval(data);
                var cpt = 0;
                
                while (cpt<JSONObject.length)
                {
                    resourceTypeArray[cpt]=JSONObject[cpt];
                    cpt++;
                }
                subItems = generateSubItems();
                createTree();
            },
        error: function(data){
            alert("resource type loading failed");
        }
       });
        
        //créé après la récupération de resourceType~ à changer
        function createTree()
        {     
            $("#ct_tree").dynatree({
                title: "myTree",
                initAjax:{url:Routing.generate('claro_resource_JSON_node',{'id':0, 'idRepository': idUserRepository})},
                clickFolderMode: 1,
                onLazyRead: function(node){
                    node.appendAjax({url:Routing.generate('claro_resource_JSON_node', {'id':node.data.key, 'idRepository': idUserRepository})});
                },
                onCreate: function(node, span){
                    bindContextMenu(node);
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
                        if(node.isDescendantOf(sourceNode)){
                            return false;
                        }
                        else{
                        sendRequest("claro_resource_move", {"idChild": sourceNode.data.key, "idParent": node.data.key});
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
    
    function submissionHandler(data, route, routeParameters)
    {
        console.debug(data);
        try{
            var JSONObject = JSON.parse(data);
            var node = $("#ct_tree").dynatree("getTree").selectKey(routeParameters.id);
            if(JSONObject.type != 'directory')
            {
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
           // $('#ct_form').dialog("close");
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
    
    function successHandler(){
        alert("success");
    }
    
    function createFormDialog(type, id){
            console.debug(type);
            var route = Routing.generate('claro_resource_form_resource', {'type':type, 'id':id});
            $.ajax({
                type: 'POST',
                url: route,
                cache: false,
                success: function(data){
                    $('#ct_form').empty();
                    $('#ct_form').append(data);
                    //ici je change l'event du submit
                    $("#generic_form").submit(function(e){
                        e.preventDefault();
                        sendForm("claro_resource_add_resource",  {'type':type, 'id':id}, document.getElementById("generic_form"));
                        });
                    }
                });
        }    
         
    function deleteNode(node)
    {
        $.ajax({
        type: 'POST',
        url: Routing.generate('claro_resource_remove_workspace',{'resourceId':node.data.key}),
        success: function(data){
            if(data=="success")
            {
                node.remove();
            }       
        }
        });
    }
    
    function openNode(node)
    {
        window.location = Routing.generate('claro_resource_open',{'id':node.data.key});
    }   
    
    function viewNode(node)
    {
        window.location = Routing.generate('claro_resource_default_click',{'id':node.data.key});
    }
    
   function bindContextMenu(node){
    
    var menuDefaultOptions = {
    selector: 'a.dynatree-title', 
        callback: function(key, options) {
            switch(key)
            {
                case "open":
                    openNode(node);
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
        "new": {name: "new",
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
       "view": {name: "view", accesskey:"v"},
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
        xhr.onload = function(e){submissionHandler(xhr.responseText, route, routeParameters)};
        xhr.send(formData);
    }
    
    //pas terrible la création... une manière plus propre de le faire en js ?
    function generateSubItems()
    {
        var cpt = 0;
        var subItems='';
        subItems+='{'
        while(cpt<resourceTypeArray.length)
        {
            var name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            subItems+= '"'+resourceTypeArray[cpt].type+'": {"name":"'+translation+'"}';                
            cpt++;
            if(cpt<resourceTypeArray.length)
            {
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
            +"<div id='ct_form'></div><div id='ct_tree'></div>"
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
        while(cpt < resourceTypeArray.length)
        {
            name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            console.debug(translation);
            resourceTypeArray[cpt].type=translation;
            cpt++;    
        }
    }
});

function getUserRepositoryId()
{
    $.ajax({
        type: 'POST',
        url: Routing.generate("claro_current_user_repository_id"),
        cache: false,
        success: function(data){
            idUserRepository = data;
        },
        error: function(xhr){
            alert(xhr.status);
        }
    });
}