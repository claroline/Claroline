dojo.provide("claroline.workspace.test.workspaceTest");

//dojo.require("dojo.robot");
 dojo.require("dijit.dijit"); // optimize: load dijit layer
 dojo.require("dijit.robotx"); // load the robot

dojo.ready(function(){
    
    doh.robot.initRobot('testurl');
    
    doh.register("doh.robot",{
            name:"robotTest",
            timout: 3000,
            setUp: function(){
                console.debug("TEST INIT");
            },
            runTest: function(){
                console.debug("TEST START");
                doh.assertTrue(true);
                console.debug("IT WORKS");           
            }
    });
    doh.run();
});