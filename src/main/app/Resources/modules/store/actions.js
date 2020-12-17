import invariant from 'invariant'

// generator for very simple action creators (see redux doc)
function makeActionCreator(type, ...argNames) {
  return (...args) => {
    let action = { type }
    argNames.forEach((arg, index) => {
      invariant(args[index] !== undefined, `${argNames[index]} is required`)
      action[argNames[index]] = args[index]
    })

    return action
  }
}

/**
 * Generates an action that will only be caught by a specific instance.
 *
 * @param {string} type         - the action
 * @param {string} instanceName - the instance
 *
 * @return {string}
 */
function makeInstanceAction(type, instanceName) {
  return `${type}/${instanceName}`
}

// [Advanced use]
function makeInstanceActionCreator(type, ...argNames) {
  return (instanceName, ...args) => {
    let action = {
      type: makeInstanceAction(type, instanceName)
    }

    argNames.forEach((arg, index) => {
      invariant(args[index] !== undefined, `${argNames[index]} is required`)
      action[argNames[index]] = args[index]
    })

    return action
  }
}

export {
  makeInstanceAction,
  makeActionCreator,
  makeInstanceActionCreator
}
