import update from 'immutability-helper'
import invariant from 'invariant'
import React from 'react'
import uuid from 'uuid'

// re-export immutability-helper with a custom delete command
update.extend('$delete', (property, object) => {
  const newObject = update(object, {[property]: {$set: undefined}})
  delete newObject[property]
  return newObject
})

export {update}

// generator for very simple action creators (see redux doc)
export function makeActionCreator(type, ...argNames) {
  return (...args) => {
    let action = { type }
    argNames.forEach((arg, index) => {
      invariant(args[index] !== undefined, `${argNames[index]} is required`)
      action[argNames[index]] = args[index]
    })
    return action
  }
}

// counter for id generation
let lastGeneratedIds = []

// generate a temporary id string
export function makeId() {
  lastGeneratedIds.push(uuid())

  return lastGeneratedIds[lastGeneratedIds.length - 1]
}

// return the last generated id (mainly for test purposes)
export function lastId() {
  return lastGeneratedIds[lastGeneratedIds.length - 1]
}

// test purpose only
export function lastIds(count) {
  if (count > lastGeneratedIds.length) {
    throw new Error(
      `Cannot access last ${count} ids, only ${lastGeneratedIds.length} were generated`
    )
  }

  const ids = []

  for (let i = lastGeneratedIds.length - count; i < lastGeneratedIds.length; ++i) {
    ids.push(lastGeneratedIds[i])
  }

  return ids
}

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

export function extractTextFromHtml(html) {
  if (!html) {
    return ''
  }

  const wrapper = document.createElement('div')
  wrapper.innerHTML = html

  return wrapper.textContent || isAllowedHtmlTag(wrapper.innerHTML)
}

export function isAllowedHtmlTag(html) {
  const allowedTags = ['img', 'audio', 'iframe', 'video']
  return allowedTags.some((tag) => {
    return html.indexOf(tag) >= 0
  })
}

export function makeInputPropType(valueType) {
  return React.PropTypes.shape({
    input: React.PropTypes.shape({
      value: valueType
    }).isRequired
  })
}
