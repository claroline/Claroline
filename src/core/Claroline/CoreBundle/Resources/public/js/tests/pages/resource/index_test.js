module("Claroline", {
  setup: function() {
    // opens the page you want to test
    S.open("http://localhost/clarotest/Claroline/web/app_dev.php/resource/directory");
  }
})

test("page has content", function(){
    S('#username').visible().type('[ctrl]a\bbob');
    S('#password').visible().type('[ctrl]a\bbob');
    S('#submit').click()

  ok( S("body *").size(), "There be elements in that there body")
})