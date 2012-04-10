 define([
     "dijit/Tree",  
     "dojox/data/JsonRestStore",     
     "dojo/domReady!", 
     "claroline/resource/resourceTree"
 ],function(Tree, JsonRestStore)
{
     startup = function(){
        var path = Routing.generate("claro_resource_fakeroute");
        path+="/";
        var myStore = JsonRestStore({target:path, labelAttribute:"name"});
        

        var myModel = new dijit.tree.ForestStoreModel({
           store: myStore,
           deferItemLoadingUntilExpand: true,
           query: "root",
           childrenAttrs: ["children"]
        });

        var myTree = Tree({
            model: myModel,
            persist:false
           // dndController:"dijit.tree.dndSource"
            }, "treeNode");
        myTree.startup();    
    };
    return {
        init: function(){startup();}
    };
    
});