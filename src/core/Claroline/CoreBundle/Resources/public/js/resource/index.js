     
    //var route = Routing.generate('claro_resource_JSON_node',{'id':0});  
    //todo: l'event close de la boite de dialogue
    
    //variable globales = pas bon
    resourceTypeArray = new Array();
    subItems = {};
    function openTreeDialog()
    {
        $("#treeDialog").dialog('open');
    }
    
    $(function(){
        
        createTreeDialog();
        
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
            }
       });
        
        //créé après la récupération de resourceType~ à changer
        function createTree()
        {
            $("#treeDialog").dynatree({
                title: "myTree",
                initAjax:{url:Routing.generate('claro_resource_JSON_node',{'id':0})},
                onLazyRead: function(node){
                    node.appendAjax({url:Routing.generate('claro_resource_JSON_node', {'id':node.data.key })});
                    //bindContextMenu();
                },
                onCreate: function(node, span){
                    bindContextMenu();
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
                        sendRequest("claro_resource_move", {"idChild": sourceNode.data.key, "idParent": node.data.key });
                        sourceNode.move(node, hitMode);
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
    
    //todo:submission: check si la réponse est du json
    function submissionHandler(data, route, routeParameters)
    {
        if(data!="epic fail")
        {
            var JSONObject = JSON.parse(data);
            var node = $("#treeDialog").dynatree("getTree").selectKey(routeParameters.id);
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
            $('#formDialog').dialog("close");
            $('#formDialog').empty();
        }
        else
        {
            $('#formDialog').append(data);
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
            route = Routing.generate('claro_resource_form_resource', {'type':type, 'id':id});
            $.ajax({
                type: 'POST',
                url: route,
                cache: false,
                success: function(data){
                    $('#formDialog').append(data);
                    $("#formDialog").dialog('open');
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
        url: Routing.generate('claro_resource_delete',{'id':node.data.key}),
        success: function(data){
            if(data=="delete")
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
    
    //traductions ?
    function bindContextMenu(){
        $.contextMenu({
            selector: 'span.dynatree-node', 
            callback: function(key, options) {
                //menu click events
                var m = "clicked: " + key;
                console.debug(m);
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
            "delete": {name: "delete", icon: "delete", accesskey:"d"}
            }
        });   
    }
    
    $('#formDialog').dialog({
        autoOpen: false,
        //more parameters here
        close: function(ev, ui){
            $('#formDialog').empty();}
    });
      
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
            subItems+= '"'+resourceTypeArray[cpt].type+'": {"name":"'+resourceTypeArray[cpt].type+'"}';                
            cpt++;
            if(cpt<resourceTypeArray.length)
            {
                subItems+=",";
            }       
        }    
        subItems+='}'
        object = JSON.parse(subItems);
        return object;
    }
    
    function createTreeDialog()
    {
        $('#treeDialog').dialog({
            autoOpen:false
        });
    }
});