//dojo 1.7 AMD
define([ 
    "dojo/on", 
    "claroline/misc/dialog/searchUserDialog",
    "claroline/misc/dialog/searchGroupDialog",
    "claroline/workspace/workspace_user_list"
],
function(on, UserDialog, GroupDialog){
    startup = function(workspaceId){
        
         var userDialog = new UserDialog();
         userDialog.init(workspaceId);
         var groupDialog = new GroupDialog();
         groupDialog.init(workspaceId);
         var target = dojo.byId("show_user_dialog_button");
         on(target, "click", function(event){
             userDialog.show();
         });
         target = dojo.byId("show_group_dialog_button");
         on(target, "click", function(event){
             groupDialog.show();
         });         
    };
    return {
           init: function(workspaceId){startup(workspaceId);}
    };
});
    
   

   