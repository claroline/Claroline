import {createSelector} from 'reselect'

const offline = (state) => state.noServer || state.testMode
const paper = (state) => state.paper
const currentStepId = (state) => state.currentStep.id
const answers = (state) => state.answers
const quizMaxAttempts = (state) => state.quiz.parameters.maxAttempts
const quizEndMessage = (state) => state.quiz.parameters.endMessage
const quizEndNavigation = (state) => state.quiz.parameters.endNavigation
const showEndConfirm = (state) => state.quiz.parameters.showEndConfirm
const showFeedback = (state) => state.quiz.parameters.showFeedback
const feedbackEnabled = state => state.currentStep.feedbackEnabled
const showCorrectionAt = state => state.quiz.parameters.showCorrectionAt
const correctionDate = state => state.quiz.parameters.correctionDate
const hasEndPage = state => state.quiz.parameters.showEndPage

const steps = createSelector(
  paper,
  (paper) => paper.structure.steps
)

/**
 * Gets the definition of the step that is currently played.
 */
const currentStep = createSelector(
  steps,
  currentStepId,
  (steps, currentStepId) => steps.find(step => step.id === currentStepId)
)

const currentStepIndex = createSelector(
  steps,
  currentStep,
  (steps, currentStep) => steps.indexOf(currentStep) + 1
)

/**
 * Retrieves the picked items for a step.
 */
const currentStepItems = createSelector(
  currentStep,
  (currentStep) => currentStep.items
)

const currentStepOrder = createSelector(
  steps,
  currentStep,
  (steps, currentStep) => steps.indexOf(currentStep)
)

const currentStepNumber = createSelector(
  currentStepOrder,
  (currentStepOrder) => currentStepOrder + 1
)

/**
 * Gets an existing answer to a question.
 */
const currentStepAnswers = createSelector(
  currentStepItems,
  answers,
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

const currentStepMaxAttempts = createSelector(
  currentStep,
  (currentStep) => currentStep.parameters.maxAttempts
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
  currentStepItems,
  currentStepAnswers,
  currentStepIndex,
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
