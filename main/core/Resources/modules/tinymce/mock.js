
function mock() {
  window.tinymce = {
    get: () => ({
      on: () => {},
      setContent: () => {},
      destroy: () => {}
    })
  }
}

export {
  mock
}
