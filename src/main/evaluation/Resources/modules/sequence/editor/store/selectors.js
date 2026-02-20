import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as formSelectors} from '#/main/app/content/form'

import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store'

const STORE_NAME = 'sequenceEditor'

/**
 * Get the path of the current sequence editor.
 * Used to create additional routing in the editor.
 */
const path = createSelector(
  [sequenceSelectors.path],
  (sequencePath) => sequencePath + '/edit'
)

const errors = (state) => formSelectors.errors(formSelectors.form(state, STORE_NAME))
const data = (state) => formSelectors.data(formSelectors.form(state, STORE_NAME))

const workspace = createSelector(
  [data],
  (data) => data.workspace
)

const workspaceId = createSelector(
  [workspace],
  (workspace) => workspace.id
)

const steps = createSelector(
  [data],
  (data) => data.steps || []
)

const totalEstimatedDuration = createSelector(
  [steps],
  (steps) => {
    const sum = (stepsList) =>
      (stepsList || []).reduce((total, step) => {
        const primary = step && step.primaryResource
        const own = (primary && primary.estimatedDuration) ? primary.estimatedDuration : 0
        const children = step && step.children ? step.children : []
        return total + own + sum(children)
      }, 0)

    return sum(steps)
  }
)

const assignments = createSelector(
  [data],
  (data) => data.assignments || []
)

const requirements = createSelector(
  [data],
  (data) => data.requirements || []
)

const numbering = createSelector(
  [data],
  (data) => get(data, 'display.numbering')
)

export const selectors = {
  STORE_NAME,
  path,
  errors,
  data,
  workspace,
  workspaceId,
  steps,
  totalEstimatedDuration,
  assignments,
  requirements,
  numbering
}
