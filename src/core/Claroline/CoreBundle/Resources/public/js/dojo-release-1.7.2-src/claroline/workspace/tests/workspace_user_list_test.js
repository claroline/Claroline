dojo.provide("claroline.workspace.workspaceTest");

dojo.require("dojo.robot");
dojo.require("dojo.ready!");

doh.register("doh.robot", [
    {
        name:"robotTest",
        timout: 3000,
        setUp: function(){
            console.debug("TEST INIT");
            //doh.robot.initRobot(/*url*/);
        },
        runTest: function(){
            console.debug("TEST START");
            doh.assertTrue(true);
            console.debug("IT WORKS");
            //needed for ajax testing
            /*var d = new doh.Deferred();
           
            console.debug("ROBOT CLICK");
            var button = dojo.byId("show_user_dialog_button");
            dojo.robot.mouseMoveAt(button);
            dojo.robot.mouseClick({left:true},500);
            console.debug("ROBO CLICKED");                      
            var dialog = dojo.byId("user_search_div");
            console.debug(dialog)
            console.debug("A WILD DIALOG APPEARS");*/
        }
    }
]); 
doh.run();