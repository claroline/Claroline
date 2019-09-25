import {constants} from '#/plugin/exo/resources/quiz/constants'
import {getItem} from '#/plugin/exo/items'
import cloneDeep from 'lodash/cloneDeep'

function refreshIdentifiers(item) {
  return getItem(item.type).then(definition => {
    return definition.refreshIdentifiers(cloneDeep(item))
  })
}

// TODO : find a way to merge with path numbering

function getNumbering(type, stepIndex, itemIndex) {
  let numbering = [
    stepIndex
  ]

  if (undefined !== itemIndex) {
    numbering.push(itemIndex)
  }

  switch (type) {
    /**
     * The numbering label is a number.
     */
    case constants.NUMBERING_NUMERIC:
      return numbering
        .map(idx => idx + 1)
        .join('.')

    /**
     * The numbering label is a letter.
     */
    case constants.NUMBERING_LITERAL:
      return numbering
        .map(idx => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[idx])
        .join('.')

    /**
     * The numbering feature is disabled.
     */
    default:
      return null
  }
}

export {
  getNumbering,
  refreshIdentifiers
}
