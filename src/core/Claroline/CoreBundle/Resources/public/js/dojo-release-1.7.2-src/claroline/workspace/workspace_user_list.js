//dojo 1.7 AMD
nbUsersIterations = 0;

define([ "dojo/on", "dojo/_base/xhr", "dojo/dom-construct", "dojo/dom", "dijit/form/CheckBox", "dijit/Dialog", "dojo/NodeList-traverse", "claroline/workspace/workspace_user_list"],
function(on, xhr, domConstruct, dom, CheckBox){
    startup = function(workspaceId){
         var target = dojo.byId("show_user_dialog_button");
         var userDialog = this.createUserDialog();
         //TODO 1st iteration
         //set listener un button click
         on(target, "click", function(event){
             userDialog.show();
         });
         target = dojo.byId("add_next_users"); 
         on(target, "click", function(event){
             getNextUsers('claro_workspace_ajax_get_add_users_workspace', {'id': workspaceId , 'nbIteration': nbUsersIterations}, workspaceId);
         });   
         target = dojo.byId("search_user_button");
         
         on(target, "click", function(event){
             sendSearchUsers('claro_workspace_ajax_generic_user_search_workspace', {'search': dojo.byId("search_user_txt").value, 'id': workspaceId}, workspaceId);
         });
    },
    setContentUserDialog = function(){
        var content = "<div id ='user_search_div'/>";
        content+="<input type='text' id='search_user_txt' placeholder='search' >";
        content+="<input type='button' id='search_user_button' value ='search'/><br>";
        content+="</div>";
        content+="<div id='user_checkboxes' style='height:160px;overflow-y:scroll;border:1px solid #769dc4;padding:0 10px;width:600px;'>";
        content+="<form>"
        content+="<table id='user_table_checkboxes'></table>";
        content+="</div>";
        content+="<input type='button' id='add_next_users' value='add next'/>";
        content+="</form>";
        
        return content;
    },  
    getNextUsers = function(route, routeParams, workspaceId){
        xhr.post({
            url:Routing.generate(route, routeParams),
            load: function(data){
                nbUsersIterations++;
                var JSONObject = JSON.parse(data);
                setChkBox(JSONObject, workspaceId);
            },
            error: function(e){
                alert("ajax_error");
            }
        });
    },
    setChkBox = function(JSONObject, workspaceId){
        for(var i=0; i<JSONObject.users.length; i++)
        {
            var box = new CheckBox({
                id: "ubox"+JSONObject.users[i].id,
                value: JSONObject.users[i].id,
                name: "checkbox",
                checked: false,
                userId: JSONObject.users[i].id,
                onChange: function(isChecked){
                    if(isChecked){
                        sendAddUser('claro_workspace_ajax_add_user_workspace', {'userId': this.get('value'), 'workspaceId': workspaceId});
                    }
                    else
                    {
                        sendRemoveUser('claro_workspace_ajax_delete_user_workspace', {'userId': this.get('userId'), 'workspaceId': workspaceId}, this.get('userId'));
                    }
                }
            });  
            var table = dom.byId("user_table_checkboxes");
            var row = dojo.create("tr", {"class": "table_row_user"}, table);
            var cell = dojo.create("td", null, row);
            box.placeAt(cell);
            dojo.create("td", {innerHTML: JSONObject.users[i].username}, row);
            dojo.create("td", {innerHTML: JSONObject.users[i].firstName}, row);
            dojo.create("td", {innerHTML: JSONObject.users[i].lastName}, row);
        }
    },
    sendAddUser = function(route, routeParams){
        xhr.post({
           url: Routing.generate(route, routeParams),
           load: function(data){
             var JSONObject = JSON.parse(data);
             var li = dojo.create("li", {"class": "row_user", "id":"user_"+JSONObject.users.id, innerHTML: JSONObject.users.username}, dojo.byId("workspace_users"));
             dojo.create("a", {"href": Routing.generate("claro_workspace_delete_user_workspace", routeParams), "id":"link_delete_user_"+JSONObject.users.id, innerHTML: "delete"}, li);
           },
           error: function(e){
               alert("ajax_error");
           }
        });
    },
    sendRemoveUser = function(route, routeParams, userId){
        xhr.post({
           url: Routing.generate(route, routeParams),
           load: function(data){
               row = dom.byId('user_'+userId);
               alert(Routing.generate(route, routeParams));
               domConstruct.destroy(row); 
           },
           error: function(e){
               alert("ajax_error");
           }
        });
    },
    sendSearchUsers = function(route, routeParams, workspaceId){
        xhr.post({
            url: Routing.generate(route, routeParams),
            load: function(data){
                var JSONObject = JSON.parse(data);
                var rows = dojo.query('.table_row_user');
                //remove from the dojo hash id
                dojo.forEach(dijit.findWidgets(dojo.byId('user_table_checkboxes')), function(w) {
                    w.destroyRecursive();
                });
                dojo.forEach(rows, function(row) {
                    domConstruct.destroy(row);
                });
                
                setChkBox(JSONObject, workspaceId);
            },
            error: function(e){
                alert("ajax_error");
            }                            
        })
    },
    createUserDialog = function(){
        var content = setContentUserDialog();
        var userDialog = new dijit.Dialog({
            title:"my dialog",
            content:content,
            draggable:true,
            open:false,
            onCancel: function(){
                var rows = dojo.query('.table_row_user');
                //remove from the dojo hash id
                dojo.forEach(dijit.findWidgets(dojo.byId('user_table_checkboxes')), function(w) {
                    w.destroyRecursive();
                });
                dojo.forEach(rows, function(row) {
                    domConstruct.destroy(row);
                });
                nbUsersIterations = 0;   
            }
        });        
        return userDialog;           
    };
    return {
           init: function(workspaceId){ 
           startup(workspaceId);
        }
    };
});
    
   

   