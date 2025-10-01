import {createSelector} from 'reselect'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {selectors as toolSelectors} from '#/main/core/tool'

import {constants} from '#/main/evaluation/constants'
import {route} from '#/main/evaluation/sequence'
import {flattenSteps, getNumbering} from '#/main/evaluation/sequence/utils'

const STORE_NAME = 'evaluationSequence'

const store = (state) => state[STORE_NAME] || {}

const data = createSelector(
  [store],
  (store) => store.data
)

const sequence = createSelector(
  [data],
  (data) => data.sequence
)

const workspace = createSelector(
  [sequence],
  (sequence) => sequence ? sequence.workspace : null
)

const published = createSelector(
  [sequence],
  (sequence) => get(sequence, 'meta.published', false)
)

const archived = createSelector(
  [sequence],
  (sequence) => get(sequence, 'meta.archived', false)
)

const path = createSelector(
  [toolSelectors.path, sequence],
  (basePath, sequence) => {
    if (sequence) {
      return route(sequence, null, basePath)
    }

    return basePath
  }
)

const id = createSelector(
  [sequence],
  (sequence) => sequence.id
)

const steps = createSelector(
  [sequence],
  (sequence) => sequence.steps || []
)

const empty = createSelector(
  [steps],
  (steps) => 0 === steps.length
)

const orderedSteps = createSelector(
  [steps],
  (steps) => flattenSteps(steps)
)

const totalSteps = createSelector(
  [orderedSteps],
  (orderedSteps) => orderedSteps.length
)

const totalActivities = createSelector(
  [orderedSteps],
  (orderedSteps) => orderedSteps.reduce((acc, current) => {
    if (current.primaryResource) {
      acc++
    }

    return acc
  }, 0)
)

const currentStepSlug = createSelector(
  [store],
  (store) => store.currentStep
)

const currentStepIndex = createSelector(
  [currentStepSlug, orderedSteps],
  (currentStepSlug, orderedSteps) => {
    if (!currentStepSlug) {
      return 0
    }

    return orderedSteps.findIndex(step => step.slug === currentStepSlug)
  }
)

const currentStep = createSelector(
  [currentStepIndex, orderedSteps],
  (currentStepIndex, orderedSteps) => orderedSteps[currentStepIndex]
)


const allSecondaryResources = createSelector(
  [orderedSteps],
  (orderedSteps) => orderedSteps.reduce((secondaryResources, current) => current.secondaryResources ?
    secondaryResources.concat(current.secondaryResources) :
    secondaryResources
  , [])
)

const navigationEnabled = createSelector(
  [store],
  (store) => store.navigationEnabled
)

const evaluation = createSelector(
  [data],
  (data) => data.userEvaluation
)

const progression = createSelector(
  [data],
  (data) => data.progression
)

const userFeedback = createSelector(
  [sequence, evaluation],
  (sequence, evaluation) => {
    switch (evaluation.status) {
      case constants.EVALUATION_STATUS_COMPLETED:
        return get(sequence, 'evaluation.endMessage', null)
      case constants.EVALUATION_STATUS_PASSED:
        return get(sequence, 'evaluation.successMessage', null) || get(sequence, 'evaluation.endMessage', null)
      case constants.EVALUATION_STATUS_FAILED:
        return get(sequence, 'evaluation.failedMessage', null) || get(sequence, 'evaluation.endMessage', null)
      default:
        return null
    }
  }
)

const totalScore = createSelector(
  [sequence],
  (sequence) => get(sequence, 'evaluation.scoreTotal', null)
)

const hasEvaluation = createSelector(
  [],
  () => true
)

const hasScore = createSelector(
  [totalScore],
  (totalScore) => !!totalScore
)

const successCondition = createSelector(
  [sequence],
  (sequence) => get(sequence, 'evaluation.successCondition', null)
)

const successScore = createSelector(
  [sequence],
  (sequence) => get(sequence, 'evaluation.successCondition.score', null)
)

const countSuccessCondition = createSelector(
  [successCondition],
  (successCondition) => {
    if (isEmpty(successCondition)) {
      return 0
    }

    return Object.keys(successCondition).length
  }
)

const hasSuccessCondition = createSelector(
  [successCondition],
  (successCondition) => !!successCondition
)

const sequenceNumbering = createSelector(
  [sequence],
  (sequence) => get(sequence, 'display.numbering', 'none')
)

const stepNumbering = createSelector(
  [steps, sequenceNumbering, (state, currentStep) => currentStep],
  (steps, sequenceNumbering, currentStep) => getNumbering(sequenceNumbering, steps, currentStep)
)

export const selectors = {
  STORE_NAME,

  sequence,
  archived,
  published,
  workspace,
  path,
  id,
  steps,
  totalSteps,
  totalActivities,
  orderedSteps,
  empty,
  currentStep,
  currentStepSlug,
  currentStepIndex,
  allSecondaryResources,
  navigationEnabled,
  sequenceNumbering,
  stepNumbering,
  // current user progression
  evaluation,
  progression,
  userFeedback,
  // evaluations params
  hasEvaluation,
  hasScore,
  totalScore,
  hasSuccessCondition,
  countSuccessCondition,
  successCondition,
  successScore
}
