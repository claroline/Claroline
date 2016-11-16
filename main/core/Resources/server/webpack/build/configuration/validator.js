function validate(bundle) {
  if (bundle.actions) {
    validateAction(bundle.actions)
  }

  return true
}

function validateAction(actions) {
  var required = ['name', 'type']
  actions.forEach(action => {
    required.forEach(property => {
      if (!action.hasOwnProperty(property)) {
        throw new Error(`The property ${property} is required for each action`)
      }
    })
  })
}

module.exports = {
  validate
}
