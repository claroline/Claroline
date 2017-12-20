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

// [Advanced use]
function makeInstanceActionCreator(type, ...argNames) {
  return (instanceName, ...args) => {
    let action = {
      type: type+'/'+instanceName
    }

    argNames.forEach((arg, index) => {
      invariant(args[index] !== undefined, `${argNames[index]} is required`)
      action[argNames[index]] = args[index]
    })

    return action
  }
}

export {
  makeActionCreator,
  makeInstanceActionCreator
}
