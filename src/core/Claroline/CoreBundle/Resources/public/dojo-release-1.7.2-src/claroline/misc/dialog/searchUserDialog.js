nbUsersIterations=0;
define([
    "dojo/_base/declare",
    "dojo/text!claroline/misc/dialog/user_dialog_template.html", 
    "dojo/i18n!claroline/misc/nls/translations", 
    "dijit/Dialog",
    "dojo/dom-construct",
    "dojo/on",
    "dojo/_base/xhr",
    "dijit/form/CheckBox",
    "dojo/dom"
],
function(declare, template, translation, Dialog, domConstruct, on, xhr, CheckBox, dom){
    getNextUsers = function(route, routeParams, workspaceId){
        xhr.post({
            url:Routing.generate(route, routeParams),
            load: function(data){
                nbUsersIterations++;
                var JSONObject = JSON.parse(data);
                setUserChkBox(JSONObject, workspaceId);
            },
            error: function(e, ioargs){
                switch(ioargs.xhr.status)
                {
                    case 403:
                        window.location.reload();
                        break;
                    defaults:
                        alert(e);
                }
            }
        });
    },
    setUserChkBox = function(JSONObject, workspaceId){
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
           error: function(e, ioargs){
                switch(ioargs.xhr.status)
                {
                    case 403:
                        window.location.reload();
                        break;
                    defaults:
                        alert(e);
                }
            }
        });
    },
    sendRemoveUser = function(route, routeParams, userId){
        xhr.post({
           url: Routing.generate(route, routeParams),
           load: function(data){
               row = dom.byId('user_'+userId);
               domConstruct.destroy(row); 
           },
           error: function(e, ioargs){
                switch(ioargs.xhr.status)
                {
                    case 403:
                        window.location.reload();
                        break;
                    defaults:
                        alert(e);
                }
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
            error: function(e, ioargs){
                switch(ioargs.xhr.status)
                {
                    case 403:
                        window.location.reload();
                        break;
                    defaults:
                        alert(e);
                }
            }                         
        })
    };
    return declare("claroline.misc.dialog.searchUserDialog", Dialog, {
        title: "user dialog",
        content: template,
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
        },
        init: function(workspaceId){
            var target = dojo.byId("add_next_users"); 
            on(target, "click", function(event){
                getNextUsers('claro_workspace_ajax_get_add_users_workspace', {'id': workspaceId , 'nbIteration': nbUsersIterations}, workspaceId);
            });   
             
             target = dojo.byId("search_user_button");
             on(target, "click", function(event){
                sendSearchUsers('claro_workspace_ajax_generic_user_search_workspace', {'search': dojo.byId("search_user_txt").value, 'id': workspaceId}, workspaceId);
             });
             
            getNextUsers('claro_workspace_ajax_get_add_users_workspace', {'id': workspaceId , 'nbIteration': nbUsersIterations}, workspaceId);
              
            dojo.byId("add_next_users").value=translation.NEXT;
            dojo.byId("search_user_button").value=translation.SEARCH;
        }
    });
});