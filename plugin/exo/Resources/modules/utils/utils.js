import update from 'immutability-helper'

// re-export immutability-helper with a custom delete command
update.extend('$delete', (property, object) => {
  const newObject = update(object, {[property]: {$set: undefined}})
  delete newObject[property]
  return newObject
})

export {update}

export function makeItemPanelKey(itemType, itemId) {
  return `item-${itemType}-${itemId}`
}

export function makeStepPropPanelKey(stepId) {
  return `step-${stepId}-properties`
}

export function getIndex(array, element) {
  if (!Array.isArray(array)) {
    throw new Error(`Excepted array, got ${typeof array}`)
  }

  const index = array.indexOf(element)

  if (index === -1) {
    const arrString = JSON.stringify(array, null, 2)
    const elString = JSON.stringify(element, null, 2)
    throw new Error(
      `Cannot get index of element\nArray:\n${arrString}\nElement:\n${elString}`
    )
  }

  return index
}

// todo : import directly from core instead of reexport it
export {
  makeId,
  refreshIds,
  lastId,
  lastIds
} from '#/main/core/scaffolding/id'
