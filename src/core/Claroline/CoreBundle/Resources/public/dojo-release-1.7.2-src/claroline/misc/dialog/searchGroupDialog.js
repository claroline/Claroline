nbGroupsIterations=0;
define([
    "dojo/_base/declare",
    "dojo/text!claroline/misc/dialog/group_dialog_template.html", 
    "dojo/i18n!claroline/misc/nls/translations", 
    "dijit/Dialog",
    "dojo/dom-construct",
    "dojo/on",
    "dojo/_base/xhr",
    "dijit/form/CheckBox",
    "dojo/dom"
],
function(declare, template, translation, Dialog, domConstruct, on, xhr, CheckBox, dom){
    getNextGroups= function(route, routeParams, workspaceId){
        xhr.post({
            url:Routing.generate(route, routeParams),
            load: function(data){
                nbGroupsIterations++;
                var JSONObject = JSON.parse(data);
                setGrpChkBox(JSONObject, workspaceId);
            },
            error: function(e, ioargs){
                switch(ioargs.xhr.status)
                {
                    case 403:
                        window.location.reload();
                        break;
                    default:
                        alert(e);
                }
            }
        });
    },
    setGrpChkBox = function(JSONObject, workspaceId){
        for(var i=0; i<JSONObject.groups.length; i++)
        {
            var box = new CheckBox({
                id: "gbox"+JSONObject.groups[i].id,
                value: JSONObject.groups[i].id,
                name: "checkbox",
                checked: false,
                groupId: JSONObject.groups[i].id,
                onChange: function(isChecked){
                    if(isChecked){
                        sendAddGroup('claro_workspace_ajax_add_group_workspace', {'groupId': this.get('value'), 'workspaceId': workspaceId});
                    }
                    else
                    {
                        sendRemoveGroup('claro_workspace_ajax_delete_group_workspace', {'groupId': this.get('groupId'), 'workspaceId': workspaceId}, this.get('groupId'));
                    }
                }
            });  
            var table = dom.byId("group_table_checkboxes");
            var row = dojo.create("tr", {"class": "table_row_group"}, table);
            var cell = dojo.create("td", null, row);
            box.placeAt(cell);
            dojo.create("td", {innerHTML: JSONObject.groups[i].name}, row);
        }
    },
    sendAddGroup = function(route, routeParams){
        xhr.post({
           url: Routing.generate(route, routeParams),
           load: function(data){
             var JSONObject = JSON.parse(data);
             var li = dojo.create("li", {"class": "row_group", "id":"group_"+JSONObject.groups.id, innerHTML: JSONObject.groups.name}, dojo.byId("workspace_groups"));
             dojo.create("a", {"href": Routing.generate("claro_workspace_delete_group_workspace", routeParams), "id":"link_delete_group_"+JSONObject.groups.id, innerHTML: "delete"}, li);
           },
           error: function(e, ioargs){
                switch(ioargs.xhr.status)
                {
                    case 403:
                        window.location.reload();
                        break;
                    default:
                        alert(e);
                }
            }
        });
    },
    sendRemoveGroup = function(route, routeParams, groupId){
        xhr.post({
           url: Routing.generate(route, routeParams),
           load: function(data){
               row = dom.byId('group_'+groupId);
               alert(Routing.generate(route, routeParams));
               domConstruct.destroy(row); 
           },
           error: function(e, ioargs){
                switch(ioargs.xhr.status)
                {
                    case 403:
                        window.location.reload();
                        break;
                    default:
                        alert(e);
                }
            }
        });
    },
    sendSearchGroup = function(route, routeParams, workspaceId){
        xhr.post({
            url: Routing.generate(route, routeParams),
            load: function(data){
                var JSONObject = JSON.parse(data);
                var rows = dojo.query('.table_row_group');
                //remove from the dojo hash id
                dojo.forEach(dijit.findWidgets(dojo.byId('group_table_checkboxes')), function(w) {
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
                    default:
                        alert(e);
                }
            }                           
        })
    };
    return declare("claroline.misc.dialog.searchGroupDialog", Dialog, {
        title: "group dialog",
        content: template,
        draggable:true,
        open:false,
        onCancel: function(){
            var rows = dojo.query('.table_row_group');
            //remove from the dojo hash id
            dojo.forEach(dijit.findWidgets(dojo.byId('group_table_checkboxes')), function(w) {
                w.destroyRecursive();
            });
            dojo.forEach(rows, function(row) {
                domConstruct.destroy(row);
            });
            nbGroupsIterations = 0;   
        },
        init: function(workspaceId){
            var target = dojo.byId("add_next_groups"); 
            on(target, "click", function(event){
                getNextGroups('claro_workspace_ajax_get_add_groups_workspace', {'id': workspaceId , 'nbIteration': nbGroupsIterations}, workspaceId);
            });   
             
             target = dojo.byId("search_group_button");
             on(target, "click", function(event){
                sendSearchGroups('claro_workspace_ajax_generic_group_search_workspace', {'search': dojo.byId("search_group_txt").value, 'id': workspaceId}, workspaceId);
             });
             
            getNextGroups('claro_workspace_ajax_get_add_groups_workspace', {'id': workspaceId , 'nbIteration': nbGroupsIterations}, workspaceId);
            dojo.byId("add_next_groups").value=translation.NEXT;
            dojo.byId("search_group_button").value=translation.SEARCH;
        }
    });
});