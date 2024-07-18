import {constants} from '#/plugin/exo/resources/quiz/constants'
import {getItem} from '#/plugin/exo/items'
import cloneDeep from 'lodash/cloneDeep'
import {makeId} from '#/main/core/scaffolding/id'

function refreshIdentifiers(item) {
  const copy = cloneDeep(item)

  copy.id = makeId()

  copy.hints = (copy.hints || []).map(hint => Object.assign({}, hint, {id: makeId()}))
  copy.objects = (copy.objects || []).map(object => Object.assign({}, object, {id: makeId()}))

  return getItem(copy.type).then(definition => {
    return definition.refreshIdentifiers(copy)
  })
}

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
