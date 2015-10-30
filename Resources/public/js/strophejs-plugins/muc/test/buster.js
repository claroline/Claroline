var config = module.exports;

config["MUC tests"] = {
  env: "browser",
  rootPath: "../../",
  libs: [
    "test-helpers/strophe.min.js",
    "test-helpers/strophe.sentinel.js"
  ],
  extensions: [
    require("when")
  ],
  sources: [
    "muc/strophe.muc.js"
  ],
  tests: [
    "muc/test/*-test.js"
  ],
  testHelpers: [
      "test/helpers.js"
  ]
};
