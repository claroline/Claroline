import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {makeId} from '#/main/app/utils/id'
import {toKey} from '#/main/app/utils/text'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_STEP_POSITION} from '#/main/evaluation/sequence/editor/modals/position'
import {
  getStepPath,
  getStepSlug,
  getStepParent,
  getFormDataPart,
  getStepTitle
} from '#/main/evaluation/sequence/editor/utils'
import {flattenSteps} from '#/main/evaluation/sequence/utils'

function replaceStepIds(step, all) {
  step.id = makeId()
  step.slug = getStepSlug(all, step.slug)

  if (step.children) {
    step.children = step.children.map((child) => replaceStepIds(child, all))
  }

  return step
}

function insertStep(step, steps, position) {
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

/**
 * Adds a new step to the path.
 */
function addStep(steps, step, parentId = null) {
  const newState = cloneDeep(steps)

  let parent
  if (parentId) {
    parent = get(newState, getFormDataPart(parentId, newState, false))
  }

  const title = getStepTitle(steps, parent)
  const slug = getStepSlug(steps, toKey(title))
  const newStep = merge({
    id: makeId(),
    title: title,
    slug: slug
  }, step || {})

  if (!parent) {
    newState.push(newStep)
  } else {
    if (!parent.children) {
      parent.children = []
    }

    parent.children.push(newStep)
  }

  return newState
}

/**
 * Creates a copy af a step and push it at the requested position.
 */
function copyStep(steps, stepId, position) {
  let newState = cloneDeep(steps)

  // generate a copy of the step and its subtree
  const original = get(newState, getFormDataPart(stepId, newState, false))
  const copy = replaceStepIds(cloneDeep(original), newState)

  // put the copy at the correct position
  if (position.parent) {
    const parent = get(newState, getFormDataPart(position.parent, newState, false))

    parent.children = insertStep(copy, parent.children, position)
  } else {
    newState = insertStep(copy, newState, position)
  }

  return newState
}

/**
 * Moves a step to another position.
 */
function moveStep(steps, stepId, position) {
  let newState = cloneDeep(steps)

  // get the step to move
  const original = get(newState, getFormDataPart(stepId, newState, false))

  // remove the step from its current position
  const parent = getStepParent(stepId, newState)
  if (parent) {
    const currentPos = parent.children.findIndex(child => child.id === stepId)
    parent.children.splice(currentPos, 1)
  } else {
    const currentPos = newState.findIndex(child => child.id === stepId)
    newState.splice(currentPos, 1)
  }

  // move the step at the new position
  if (position.parent) {
    const parent = get(newState, getFormDataPart(position.parent, newState, false))

    parent.children = insertStep(original, parent.children, position)
  } else {
    newState = insertStep(original, newState, position)
  }

  return newState
}

/**
 * Removes a step from the path.
 */
function removeStep(steps, stepId) {
  const newState = cloneDeep(steps)
  const stepPath = getStepPath(stepId, newState)

  if (stepPath.length === 1) {
    newState.splice(stepPath[0], 1)
  } else {
    let step = newState[stepPath[0]]

    for (let i = 1; i < stepPath.length - 1; ++i) {
      step = step.children[stepPath[i]]
    }
    step.children.splice(stepPath[stepPath.length - 1], 1)
  }

  return newState
}

function getStepActions(steps, step, update, navigate, isCurrent = false) {
  const flatSteps = flattenSteps(steps)

  return [
    {
      name: 'add',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('step_add_child', {}, 'path'),
      callback: () => {
        const newStepId = makeId()

        // update store
        update(addStep(steps, {id: newStepId}, step.id))
        // open new step
        navigate(`/steps/${newStepId}`)
      }
    }, {
      name: 'copy',
      type: MODAL_BUTTON,
      icon: 'fa fa-fw fa-clone',
      label: trans('copy', {}, 'actions'),
      modal: [MODAL_STEP_POSITION, {
        icon: 'fa fa-fw fa-clone',
        title: trans('copy'),
        step: step,
        steps: flatSteps,
        saveLabel: trans('copy', {}, 'actions'),
        onSave: (position) => update(copyStep(steps, step.id, position))
      }]
    }, {
      name: 'move',
      type: MODAL_BUTTON,
      icon: 'fa fa-fw fa-arrows',
      label: trans('move', {}, 'actions'),
      modal: [MODAL_STEP_POSITION, {
        icon: 'fa fa-fw fa-arrows',
        title: trans('movement'),
        step: step,
        steps: flatSteps,
        saveLabel: trans('move', {}, 'actions'),
        onSave: (position) => update(moveStep(steps, step.id, position))
      }]
    }, {
      name: 'delete',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-trash',
      label: trans('delete', {}, 'actions'),
      callback: () => {
        update(removeStep(steps, step.id))

        if (isCurrent) {
          navigate('/steps')
        }
      },
      confirm: {
        title: trans('deletion'),
        subtitle: step.title,
        message: trans('step_delete_confirm', {}, 'path')
      },
      dangerous: true
    }
  ]
}

export {
  addStep,
  copyStep,
  moveStep,
  removeStep,
  insertStep,
  getStepActions
}
