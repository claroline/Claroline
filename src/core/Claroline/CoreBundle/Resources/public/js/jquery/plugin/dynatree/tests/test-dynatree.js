$(function(){

/*******************************************************************************
 * QUnit setup
 */
QUnit.log = function(result, message) {  
  if (window.console && window.console.log) {  
      window.console.log(result +' :: '+ message);  
  }  
}      

/*******************************************************************************
 * Tool functions
 */
function makeBenchWrapper(testName, callback) {
    return function() {
        var start = +new Date;
//        callback.apply(this, arguments);
        callback.call();
        var elap = +new Date - start;
        ok(true, testName + " took " + elap + " milliseconds");
    }
}


function benchmark(testName, callback) {
    // Execute callback immediately and log timing as test result.
    // This function should be called inside a test() function.
    makeBenchWrapper(testName, callback).call();
}


function timedTest(testName, callback) {
    // Same as test(testName, callback), but adds a timing assertion.
    test(testName, makeBenchWrapper(testName, callback));
}


function simulateClick(selector) {
    var e = document.createEvent("MouseEvents");
    e.initEvent("click", true, true);
    $(selector).each(function(){
        this.dispatchEvent(e);
    });
};


function addNodes(dtnode, level1, level2, level3, forceUpdate) {
    if( forceUpdate != true )
        dtnode.tree.enableUpdate(false);
    
    var key;
    for (var i=0; i<level1; i++) {
        key = "" + (i+1);
        var f = dtnode.addChild({title: "Folder_" + key,
                               key: key,
                               isFolder: true
                               });
        for (var j=0; j<level2; j++) {
            key = "" + (i+1) + "." + (j+1);
            var d = f.addChild({title: "Node_" + key,
                              key: key
                              });
            for (var k=0; k<level3; k++) {
                key = "" + (i+1) + "." + (j+1) + "." + (k+1);
                d.addChild({title: "Node_" + key,
                          key: key
                          });
            }
        }
    }
    dtnode.tree.enableUpdate(true);
}

/*******************************************************************************
 * Module Init
 */
module("Init");

test("Create dynatree", function() {
    $("#tree").dynatree({
        children: [
            {key: "_1", title: "Lazy Add 100 nodes (flat, force update)...", isFolder: true, isLazy: true, mode: "add100_flat_u" },
            {key: "_2", title: "Lazy Add 100 nodes (flat)...", isFolder: true, isLazy: true, mode: "add100_flat" },
            {key: "_3", title: "Lazy Add 1.000 nodes (flat)...", isFolder: true, isLazy: true, mode: "add1000_flat" },
            {key: "_4", title: "Lazy Add 1.000 nodes (deep)...", isFolder: true, isLazy: true, mode: "add1000_deep" },
            {key: "_5", title: "Lazy Add 10.000 nodes (deep)...", isFolder: true, isLazy: true, mode: "add10000_deep" },
            {key: "_6", title: "Lazy Add JSON file...", isFolder: true, isLazy: true, mode: "addJsonFile" },
            {key: "_7", title: "Add 1.000 nodes (flat)", isFolder: true },
            {key: "_8", title: "Add 1.000 nodes (deep)", isFolder: true }
        ],
        onSelect: function(dtnode) {
            alert("You selected " + dtnode.data.title);
        },
        onLazyRead: function(dtnode) {
            var tree = dtnode.tree;
            var start = +new Date;
            logMsg("Benchmarking mode='" + dtnode.data.mode + "'...");
            switch( dtnode.data.mode ) {
                case "add100_flat_u":
                    addNodes(dtnode, 100, 0, 0, true)
                    break;
                case "add100_flat":
                    addNodes(dtnode, 100, 0, 0)
                    break;
                case "add1000_flat":
                    addNodes(dtnode, 1000, 0, 0)
                    break;
                case "add1000_deep":
                    addNodes(dtnode, 10, 10, 10)
                    break;
                case "add10000_deep":
                    addNodes(dtnode, 10, 100, 10)
                    break;
                case "addJsonFile":
                    dtnode.appendAjax({
                        url: "sample-data2.json" 
                    });
                    break;
                default:
                    throw "Invalid Mode "+ dtnode.data.mode;
            }
            logMsg("Benchmarking mode='" + dtnode.data.mode + "' done: " + (+new Date - start) + " milliseconds");
            // Return true, to show we're finished
            return true;
        }
    });
});

/*******************************************************************************
 * Module Load
 */
module("Load");

test("Add nodes to folder using API witout expanding", function() {
    expect(2);

    benchmark("1000 nodes flat", function() {
        var node = $("#tree").dynatree("getTree").getNodeByKey("_7");
        addNodes(node, 1000, 0, 0);
    });

    benchmark("1000 nodes deep", function() {
        var node = $("#tree").dynatree("getTree").getNodeByKey("_8");
        addNodes(node, 10, 10, 10);
    });
});

test(".click() top level nodes (triggers lazy loading)", function() {
    expect(3);
/*    
    benchmark("Click add100_flat_u", function() {
        $("#dynatree-id-_1").click();
    });
    benchmark("Click add100_flat", function() {
        $("#dynatree-id-_2").click();
    });
*/    
    benchmark("Click add1000_flat", function() {
        $("#dynatree-id-_3").click();
    });

    benchmark("Click add1000_deep", function() {
        $("#dynatree-id-_4").click();
    });
/*
    benchmark("Click add10000_deep", function() {
        $("#dynatree-id-_5").click();
    });
    */
    benchmark("Click addJsonFile", function() {
        $("#dynatree-id-_6").click();
    });
});
/*
timedTest(".click() add10000_deep", function() {
    $("#dynatree-id-_5").click();
});

test("Load 100 nodes (flat)", function() {
    var parent  = $("#tree").dynatree("getTree").getNodeByKey("_1");
//    addNodes(parent, 100, 0, 0)
    ok( true, "all pass" );
});
*/
/*******************************************************************************
 * Module Cleanup
 */
module("Cleanup");
/*
test("Remove children", function() {
    var root = $("#tree").dynatree("getRoot");
    for(var i = 0; i<root.childList.length; i++)
        root.childList[i].removeChildren();
//  ok( true, "all pass" );
});
*/
// --- 
});
