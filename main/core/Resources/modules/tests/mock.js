// define a global noop Routing
export function mockRouting() {
  window.Routing = {
    generate: (...args) => args[0]
  }
}

// define a global noop Translator
export function mockTranslator() {
  window.Translator = {
    trans: msg => msg,
    transChoice: msg => msg
  }
}

// define a global noop TinyMCE
export function mockTinymce() {
  window.tinymce = {
    get: () => ({
      on: () => {},
      setContent: () => {},
      destroy: () => {}
    })
  }
}
