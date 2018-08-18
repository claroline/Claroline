import {jsPlumb} from 'jsplumb'

import {
  jsPlumbDefaultConfig,
  jsPlumbEnabledConfig,
  associationTypes
} from './../enums'

export const utils = {}

/**
 * @var id solution id
 * @var set first or second set
 *
 */
utils.getSolutionData = (id, set) => {
  return set.find(item => item.id === id).data
}

utils.getJsPlumbInstance = (editEnabled) => {
  const instance = jsPlumb.getInstance()

  // configure instance
  instance.importDefaults(editEnabled ? jsPlumbEnabledConfig : jsPlumbDefaultConfig)
  instance.registerConnectionTypes(associationTypes)

  return instance
}

utils.resetJsPlumb = () => {
  jsPlumb.deleteEveryConnection()

  // use reset instead of deleteEveryEndpoint because reset also remove event listeners
  jsPlumb.reset()
}

utils.isPresentInSolutions = (firstId, secondId, solutions) => {
  return solutions.findIndex(s => s.firstId == firstId && s.secondId === secondId) !== -1
}
