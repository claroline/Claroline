define([
     "dojo/_base/declare",
     "dojo/store/JsonRest"
 ],
function(declare, JsonRest){
     return declare('claroline/resource/clarolineResourceJsonStore', JsonRest, {
     constructor: function(path){
         this.target=path;
     },
     mayHaveChildren: function(object){     
         return "children" in object;
     },
     getChildren: function(object, onComplete, onError){
         this.get(object.id).then(function(fullObject){
         object.children = fullObject.children;
          onComplete(fullObject.children);
     }, function(error){
         console.error(error);
         onComplete([]);
         });
     },
     getRoot: function(onItem, onError){
         this.get(0).then(onItem, onError);
     },
     getLabel: function(object){
         return object.name;
     },
     pasteItem: function(child, oldParent, newParent, bCopy, insertIndex){
         var store = this;    
         store.get(oldParent.id).then(function(oldParent){
             store.get(newParent.id).then(function(newParent){
                 var oldChildren = oldParent.children;
                 dojo.some(oldChildren, function(oldChild, i){
                     if(oldChild.id == child.id){
                         oldChildren.splice(i, 1);
                         return true;
                     }
                 });
                 store.put(oldParent);
                 newParent.children.splice(insertIndex || 0, 0, child);
                 store.put(newParent);
             });
         });
     },
     put: function(object, options){
         this.onChildrenChange(object, object.children);
         this.onChange(object);
         return JsonRest.prototype.put.apply(this, arguments);
     },
     remove: function(id){
         this.onDelete({id: id});
     },
     onChildrenChange: function(parent, children){},
     onChange: function(object){},
     onDelete: function(object){}  
    });
});