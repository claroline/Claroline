define([
    "dojo/_base/declare",
    "dijit/Dialog",
    "dojo/_base/xhr",
    "dojo/dom"    
],
function(declare, Dialog, xhr, dom){
   return declare("claroline.misc.dialog.searchUserDialog", Dialog, {
        title: "user dialog",
        content: sfForm,
        draggable:true,
        open:false
   }); 
});