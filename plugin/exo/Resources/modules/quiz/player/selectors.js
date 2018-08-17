import {createSelector} from 'reselect'

import {select as quizSelectors} from '#/plugin/exo/quiz/selectors'

// TODO : there are duplication with base quiz selectors

const offline = createSelector(
  [quizSelectors.noServer, quizSelectors.testMode],
  (noServer, testMode) => noServer || testMode
)
const paper = createSelector(
  [quizSelectors.resource],
  (resource) => resource.paper
)

const currentStepId = createSelector(
  [quizSelectors.resource],
  (resource) => resource.currentStep.id
)

const answers = createSelector(
  [quizSelectors.resource],
  (resource) => resource.answers
)

const quizMaxAttempts = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.maxAttempts
)
const quizEndMessage = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.endMessage
)
const quizEndNavigation = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.endNavigation
)
const showEndConfirm = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.showEndConfirm
)
const showFeedback = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.showFeedback
)
const feedbackEnabled = createSelector(
  [quizSelectors.resource],
  (resource) => resource.currentStep.feedbackEnabled || false
)
const showCorrectionAt = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.showCorrectionAt
)
const correctionDate = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.correctionDate
)
const hasEndPage = createSelector(
  [quizSelectors.parameters],
  (parameters) => parameters.showEndPage || false
)

const steps = createSelector(
  [paper],
  (paper) => paper.structure ? paper.structure.steps : []
)

/**
 * Gets the definition of the step that is currently played.
 */
const currentStep = createSelector(
  [steps, currentStepId],
  (steps, currentStepId) => steps.find(step => step.id === currentStepId)
)

/**
 * Retrieves the picked items for a step.
 */
const currentStepItems = createSelector(
  currentStep,
  (currentStep) => currentStep ? currentStep.items : []
)

const currentStepOrder = createSelector(
  [steps, currentStep],
  (steps, currentStep) => steps.indexOf(currentStep)
)

const currentStepNumber = createSelector(
  [currentStepOrder],
  (currentStepOrder) => currentStepOrder + 1
)

/**
 * Gets an existing answer to a question.
 */
const currentStepAnswers = createSelector(
  [currentStepItems, answers],
  (currentStepItems, answers) => {
    return currentStepItems.reduce((answerAcc, item) => {
      answerAcc[item.id] = Object.assign({}, answers[item.id], {
        type: item.type
      })

      return answerAcc
    }, {})
  }
)

/**
 * Retrieves the next step to play (based on the paper structure).
 */
const previous = createSelector(
  steps,
  currentStepOrder,
  (steps, currentStepOrder) => currentStepOrder - 1 >= 0 ? steps[currentStepOrder - 1] : null
)

/**
 * Retrieves the previous played step (based on the paper structure).
 */
const next = createSelector(
  steps,
  currentStepOrder,
  (steps, currentStepOrder) => currentStepOrder + 1 < steps.length ? steps[currentStepOrder + 1] : null
)

const currentStepTries = createSelector(
  answers,
  currentStepItems,
  (answers, currentStepItems) => {
    let currentTries = 0

    Object.keys(answers).forEach((questionId) => {
      if (answers[questionId].tries > currentTries && currentStepItems.indexOf(questionId) > -1) {
        currentTries = answers[questionId].tries
      }
    })

    return currentTries
  }
)

const currentStepParameters = createSelector(
  currentStep,
  (currentStep) => currentStep ? currentStep.parameters : {}
)

const currentStepMaxAttempts = createSelector(
  currentStepParameters,
  (currentStepParameters) => currentStepParameters.maxAttempts
)

const currentStepSend = createSelector(
  currentStepTries,
  currentStepMaxAttempts,
  (currentStepTries, currentStepMaxAttempts) => currentStepTries < currentStepMaxAttempts || 0 === currentStepMaxAttempts
)

export const select = {
  offline,
  paper,
  steps,
  answers,
  quizMaxAttempts,
  showFeedback,
  showEndConfirm,
  feedbackEnabled,
  currentStepId,
  currentStep,
  currentStepOrder,
  currentStepNumber,
  currentStepParameters,
  currentStepItems,
  currentStepAnswers,
  previous,
  next,
  currentStepTries,
  currentStepMaxAttempts,
  currentStepSend,
  showCorrectionAt,
  hasEndPage,
  correctionDate,
  quizEndMessage,
  quizEndNavigation
}
