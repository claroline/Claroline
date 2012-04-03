define(["dojo/store/JsonRest", "dojo/_base_xhr"],
function(JsonRestStore, xhr)
{
    getResources = function(){
        return xhr.post(
        {
           url:"todo" 
        });
    }
});