/* that one is not done yet */
define([
     "dojo/_base/declare",
     "dijit/Menu", 
     "dijit/MenuItem", 
     "dijit/PopupMenuItem", 
 ],
 function(declare, Menu, MenuItem, PopupMenuItem){
     checkClaroItemAcceptance = function(){     
 
     };
     return declare('claroline/resource/clarolineResourceExplorerMenu', Menu, {
        constructor: function(treeNode){
            this.targetNodeIds = [treeNode];
        },
        nodeId: 0,
        init: function(resourceTypes, routes){
            this.addChild(new dijit.MenuItem({
                label:"rename"
            }));
            this.addChild(new dijit.MenuItem({
                label:"delete",
                onClick: function(){
                removeResource(menu.get('nodeId'));
            }}));
            this.addChild(new dijit.MenuItem({
                label:"open"
            }));
            //add subMenus. /*this doesn't look right at all */
            /*
            dojo.forEach(resourceTypes, function(resourceType, i){
                subMenu.addChild(new dijit.MenuItem({
                label:resourceType.type,
                 onClick: function(){addResource("{{routes[resourceType.getType()]}}", "{{resourceType.getVendor()}}", "{{resourceType.getBundle()}}","{{resourceType.getType()}}", menu.get('nodeId'))}
                })); 
            });*/
            }
     });
 });


