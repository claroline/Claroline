dojo.provide("dojo.claro.test");

dojo.ready(function(){
    
    
    doh.register("tt",{
            name:"TT",
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