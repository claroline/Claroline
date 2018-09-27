const modals = {}

function registerModal(type, component) {
  if (!modals[type]) {
    modals[type] = component
  }
}

function registerModals(types) {
  types.map(type => registerModal(type[0], type[1]))
}

export {
  registerModal,
  registerModals
}
