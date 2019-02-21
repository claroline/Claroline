import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {Step} from '#/plugin/path/resources/path/prop-types'
import {getStepPath, getStepParent, getFormDataPart} from '#/plugin/path/resources/path/editor/utils'

import {
  STEP_ADD,
  STEP_COPY,
  STEP_MOVE,
  STEP_REMOVE
} from '#/plugin/path/resources/path/editor/store/actions'
import {selectors} from '#/plugin/path/resources/path/editor/store/selectors'

function replaceStepIds(step) {
  step.id = makeId()

  if (step.children) {
    step.children = step.children.map(replaceStepIds)
  }

  return step
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

const reducer = makeFormReducer(selectors.FORM_NAME, {}, {
  pendingChanges: makeReducer(false, {
    [STEP_ADD]: () => true,
    [STEP_COPY]: () => true,
    [STEP_MOVE]: () => true,
    [STEP_REMOVE]: () => true
  }),
  originalData: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.path || state
  }),
  data: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.path || state,
    [STEP_ADD]: (state, action) => {
      const newState = cloneDeep(state)

      if (!action.parentId) {
        newState.steps.push(merge({}, Step.defaultProps, {
          id: makeId(),
          title: `${trans('step', {}, 'path')} ${newState.steps.length + 1}`
        }))
      } else {
        const parentPath = getStepPath(action.parentId, newState.steps)
        const parent = get(newState, getFormDataPart(action.parentId, newState.steps))
        if (!parent.children) {
          parent.children = []
        }

        parent.children.push(merge({}, Step.defaultProps, {
          id: makeId(),
          title: `${trans('step', {}, 'path')} ${parentPath.map(i => i+1).join('.')}.${parent.children.length + 1}`
        }))
      }

      return newState
    },
    [STEP_REMOVE]: (state, action) => {
      const newState = cloneDeep(state)
      const stepPath = getStepPath(action.id, newState.steps)

      if (stepPath.length === 1) {
        newState.steps.splice(stepPath[0], 1)
      } else {
        let step = newState.steps[stepPath[0]]

        for (let i = 1; i < stepPath.length - 1; ++i) {
          step = step.children[stepPath[i]]
        }
        step.children.splice(stepPath[stepPath.length - 1], 1)
      }

      return newState
    },
    [STEP_COPY]: (state, action) => {
      const newState = cloneDeep(state)

      // generate a copy of the step and its subtree
      const original = get(newState, getFormDataPart(action.id, newState.steps))
      const copy = replaceStepIds(cloneDeep(original))

      // put the copy at the correct position
      if (action.position.parent) {
        const parent = get(newState, getFormDataPart(action.position.parent, newState.steps))

        parent.children = pushStep(copy, parent.children, action.position)
      } else {
        newState.steps = pushStep(copy, newState.steps, action.position)
      }

      return newState
    },
    [STEP_MOVE]: (state, action) => {
      const newState = cloneDeep(state)

      // get the step to move
      const original = get(newState, getFormDataPart(action.id, newState.steps))

      // remove the step from its current position
      const parent = getStepParent(action.id, newState.steps)
      if (parent) {
        const currentPos = parent.children.findIndex(child => child.id === action.id)
        parent.children.splice(currentPos, 1)
      } else {
        const currentPos = newState.steps.findIndex(child => child.id === action.id)
        newState.steps.splice(currentPos, 1)
      }

      // move the step at the new position
      if (action.position.parent) {
        const parent = get(newState, getFormDataPart(action.position.parent, newState.steps))

        parent.children = pushStep(original, parent.children, action.position)
      } else {
        newState.steps = pushStep(original, newState.steps, action.position)
      }

      return newState
    }
  })
})

export {
  reducer
}
