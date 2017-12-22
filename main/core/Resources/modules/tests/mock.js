
// define a global noop Routing
function mockRouting() {
  window.Routing = {
    generate: (...args) => args[0]
  }
}

// define a global noop Translator
function mockTranslator() {
  window.Translator = {
    trans: msg => msg,
    transChoice: msg => msg
  }
}

// define a global noop TinyMCE
function mockTinymce() {
  window.tinymce = {
    get: () => ({
      on: () => {},
      setContent: () => {},
      destroy: () => {}
    })
  }
}

export {
  mockRouting,
  mockTranslator,
  mockTinymce
}
