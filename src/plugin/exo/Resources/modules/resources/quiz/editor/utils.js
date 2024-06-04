import {makeId} from '#/main/core/scaffolding/id'
import merge from 'lodash/merge'
import {Step} from '#/plugin/exo/resources/quiz/prop-types'
import cloneDeep from 'lodash/cloneDeep'
import {trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'

function getStepSlug(steps, desiredSlug) {
  if (-1 === steps.findIndex(step => step.slug === desiredSlug)) {
    // slug is free
    return desiredSlug
  }

  let i = 1
  let newSlug = desiredSlug+'-'+i
  while (-1 !== steps.findIndex(step => step.slug === newSlug)) {
    newSlug = desiredSlug+'-'+i
    i = i+1
  }

  return newSlug
}

function createStep(stepData = {}) {
  const newId = makeId()
  return merge({id: newId, slug: newId}, Step.defaultProps, stepData)
}

function pushStep(step, steps, position) {
  const newSteps = cloneDeep(steps)

  switch (position.order) {
    case 'first':
      newSteps.unshift(step)
      break

    case 'before':
    case 'after':
      if ('before' === position.order) {
        newSteps.splice(steps.findIndex(step => step.id === position.step), 0, step)
      } else {
        newSteps.splice(steps.findIndex(step => step.id === position.step) + 1, 0, step)
      }
      break

    case 'last':
      newSteps.push(step)
      break
  }

  return newSteps
}

function pushItem(item, items, position) {
  const newItems = cloneDeep(items)

  switch (position.order) {
    case 'first':
      newItems.unshift(item)
      break

    case 'before':
    case 'after':
      if ('before' === position.order) {
        newItems.splice(items.findIndex(item => item.id === position.item), 0, item)
      } else {
        newItems.splice(items.findIndex(item => item.id === position.item) + 1, 0, item)
      }
      break

    case 'last':
      newItems.push(item)
      break
  }

  return newItems
}

/**
 * Adds a new step to the quiz.
 */
function addStep(steps) {
  const newState = cloneDeep(steps)

  const title = trans('step', {number: steps.length + 1}, 'quiz')
  const slug = getStepSlug(steps, toKey(title))

  const newStep = createStep({title: title, slug: slug})
  newState.push(newStep)

  return newState
}

/**
 * Creates a copy af a copy and push it at the requested position.
 */
function copyStep(steps, copy, position) {
  let newState = cloneDeep(steps)

  newState = pushStep(copy, newState, position)

  return newState
}

/**
 * Moves a step to another position.
 */
function moveStep(steps, stepId, position) {
  let newState = cloneDeep(steps)

  const currentPos = newState.findIndex(step => step.id === stepId)
  if (-1 !== currentPos) {
    const currentStep = newState.splice(currentPos, 1)

    newState = pushStep(currentStep[0], newState, position)
  }

  return newState
}

/**
 * Removes a step from the quiz.
 */
function removeStep(steps, stepId) {
  const newState = cloneDeep(steps)

  const stepPosition = newState.findIndex(step => step.id === stepId)
  if (-1 !== stepPosition) {
    newState.splice(stepPosition, 1)
  }

  return newState
}

function copyItem(steps, item, position) {
  const newState = cloneDeep(steps)

  const newParent = newState.find(step => step.id === position.parent)
  const newItems = newParent.items

  newParent.items = pushItem(item, newItems, position)

  return newState
}

function moveItem(steps, itemId, position) {
  const newState = cloneDeep(steps)

  const oldStep = newState.find(step => step.items.find(item => item.id === itemId))
  const currentPos = oldStep.items.findIndex(item => item.id === itemId)
  const newParent = newState.find(step => step.id === position.parent)
  const newItems = newParent.items

  if (-1 !== currentPos) {
    const currentItem = oldStep.items.splice(currentPos, 1)

    newParent.items = pushItem(currentItem[0], newItems, position)
  }

  return newState
}

export {
  getStepSlug,
  addStep,
  moveStep,
  copyStep,
  removeStep,
  copyItem,
  moveItem
}
