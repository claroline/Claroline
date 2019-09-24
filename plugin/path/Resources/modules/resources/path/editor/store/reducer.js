import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
import get from 'lodash/get'

import {makeId} from '#/main/core/scaffolding/id'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {Step} from '#/plugin/path/resources/path/prop-types'
import {getStepPath, getStepSlug, getStepParent, getFormDataPart} from '#/plugin/path/resources/path/editor/utils'

import {
  PATH_ADD_STEP,
  PATH_COPY_STEP,
  PATH_MOVE_STEP,
  PATH_REMOVE_STEP
} from '#/plugin/path/resources/path/editor/store/actions'
import {selectors} from '#/plugin/path/resources/path/editor/store/selectors'

function replaceStepIds(step, all) {
  step.id = makeId()
  step.slug = getStepSlug(all, step.slug)

  if (step.children) {
    step.children = step.children.map((child) => replaceStepIds(child, all))
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
    [PATH_ADD_STEP]: () => true,
    [PATH_COPY_STEP]: () => true,
    [PATH_MOVE_STEP]: () => true,
    [PATH_REMOVE_STEP]: () => true
  }),
  originalData: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => action.resourceData.path || state
  }),
  data: makeReducer({}, {
    /**
     * Fills form when the path data are loaded.
     *
     * @param {object} state - the path object @see Path.propTypes
     */
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => action.resourceData.path || state,

    /**
     * Adds a new step to the path.
     *
     * @param {object} state - the path object @see Path.propTypes
     */
    [PATH_ADD_STEP]: (state, action) => {
      const newState = cloneDeep(state)
      const newStep = merge({id: makeId()}, Step.defaultProps, action.step || {})

      if (!action.parentId) {
        newState.steps.push(newStep)
      } else {
        const parent = get(newState, getFormDataPart(action.parentId, newState.steps))
        if (!parent.children) {
          parent.children = []
        }

        parent.children.push(newStep)
      }

      return newState
    },

    /**
     * Creates a copy af a copy and push it at the requested position.
     *
     * @param {object} state - the path object @see Path.propTypes
     */
    [PATH_COPY_STEP]: (state, action) => {
      const newState = cloneDeep(state)

      // generate a copy of the step and its subtree
      const original = get(newState, getFormDataPart(action.id, newState.steps))
      const copy = replaceStepIds(cloneDeep(original), newState.steps)

      // put the copy at the correct position
      if (action.position.parent) {
        const parent = get(newState, getFormDataPart(action.position.parent, newState.steps))

        parent.children = pushStep(copy, parent.children, action.position)
      } else {
        newState.steps = pushStep(copy, newState.steps, action.position)
      }

      return newState
    },

    /**
     * Moves a step to another position.
     *
     * @param {object} state - the path object @see Path.propTypes
     */
    [PATH_MOVE_STEP]: (state, action) => {
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
    },

    /**
     * Removes a step from the path.
     *
     * @param {object} state - the path object @see Path.propTypes
     */
    [PATH_REMOVE_STEP]: (state, action) => {
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
    }
  })
})

export {
  reducer
}
