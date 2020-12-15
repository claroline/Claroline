/**
 * Mock router for tests purposes.
 */
function mock() {
  window.Routing = {
    generate: (...args) => args[0]
  }
}

export {
  mock
}
