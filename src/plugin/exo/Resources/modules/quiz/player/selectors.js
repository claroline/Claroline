import {createSelector} from 'reselect'
import uniq from 'lodash/uniq'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store/selectors'
import {selectors as playerSelectors} from '#/plugin/exo/resources/quiz/player/store/selectors'

// TODO : merge with new player selectors (when merging both stores)

const quizId = quizSelectors.id

const testMode = createSelector(
  [quizSelectors.resource],
  (resource) => resource.testMode || false
)

const offline = createSelector(
  [testMode],
  (testMode) => testMode
)

const paper = createSelector(
  [quizSelectors.resource],
  (resource) => resource.paper
)

const attempt = playerSelectors.attempt

const answers = createSelector(
  [quizSelectors.resource],
  (resource) => resource.answers || {}
)

const paperStructure = createSelector(
  [paper],
  (paper) => paper.structure || {}
)

const paperParameters = createSelector(
  [paperStructure],
  (paperStructure) => paperStructure.parameters || {}
)

const mandatoryQuestions = createSelector(
  [paperParameters],
  (parameters) => parameters.mandatoryQuestions || false
)
const answersEditable = createSelector(
  [paperParameters],
  (parameters) => parameters.answersEditable || false
)
const progressionDisplayed = createSelector(
  [paperParameters],
  (parameters) => parameters.progressionDisplayed || false
)
const isTimed = createSelector(
  [paperParameters],
  (parameters) => parameters.timeLimited || false
)
const duration = createSelector(
  [paperParameters],
  (parameters) => parameters.duration || 0
)
const quizNumbering = createSelector(
  [paperParameters],
  (parameters) => parameters.numbering || 'none' // todo : use constant
)

const questionNumbering = createSelector(
  [paperParameters],
  (parameters) => parameters.questionNumbering || 'none' // todo : use constant
)

const showTitles = createSelector(
  [paperParameters],
  // managing undefined is for retro-compatibility (I don't want to migrate all papers to set the parameter)
  (parameters) => parameters.showTitles === undefined ? true : parameters.showTitles
)

const showQuestionTitles = createSelector(
  [paperParameters],
  // managing undefined is for retro-compatibility (I don't want to migrate all papers to set the parameter)
  (parameters) => parameters.showQuestionTitles === undefined ? true : parameters.showQuestionTitles
)

const quizEndMessage = createSelector(
  [paperParameters],
  (parameters) => parameters.endMessage
)
const quizEndNavigation = createSelector(
  [paperParameters],
  (parameters) => parameters.endNavigation || false
)
const attemptsReachedMessage = createSelector(
  [paperParameters],
  (parameters) => parameters.attemptsReachedMessage
)
const showEndConfirm = createSelector(
  [paperParameters],
  (parameters) => parameters.showEndConfirm || false
)
const showFeedback = createSelector(
  [paperParameters],
  (parameters) => parameters.showFeedback || false
)
const showBack = createSelector(
  [paperParameters],
  (parameters) => parameters.showBack || false
)
const showStatistics = createSelector(
  [paperParameters],
  (parameters) => parameters.showStatistics || false
)
const showCorrectionAt = createSelector(
  [paperParameters],
  (parameters) => parameters.showCorrectionAt
)
const correctionDate = createSelector(
  [paperParameters],
  (parameters) => parameters.correctionDate
)
const hasEndPage = createSelector(
  [paperParameters],
  (parameters) => parameters.showEndPage || false
)
const maxAttempts = createSelector(
  [paperParameters],
  (parameters) => parameters.maxAttempts || 0
)
const maxAttemptsPerDay = createSelector(
  [paperParameters],
  (parameters) => parameters.maxAttemptsPerDay || 0
)

const hasMoreAttempts = createSelector(
  [maxAttempts, maxAttemptsPerDay, playerSelectors.userPaperCount, playerSelectors.userPaperDayCount],
  (maxAttempts, maxAttemptsPerDay, userPaperCount, userPaperDayCount) => (!maxAttempts || maxAttempts > userPaperCount) && (!maxAttemptsPerDay || maxAttemptsPerDay > userPaperDayCount)
)

const steps = createSelector(
  [paperStructure],
  (structure) => structure.steps || []
)

const currentStepId = createSelector(
  [quizSelectors.resource],
  (resource) => resource.currentStep.id
)
const feedbackEnabled = createSelector(
  [quizSelectors.resource],
  (resource) => resource.currentStep.feedbackEnabled || false
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

const items = createSelector(
  [steps],
  (steps) => [].concat(...steps.map(step => step.items || []))
)

// TODO : exclude content items
const countItems = createSelector(
  [items],
  (items) => items.length
)

const tags = createSelector(
  [items],
  (items) => uniq(items.reduce((tags, item) => tags.concat(item.tags), []))
)

const showEndStats = createSelector(
  [securitySelectors.currentUser, paperParameters],
  (currentUser, parameters) => {
    if (!parameters.hasExpectedAnswers) {
      return false
    }

    if ('none' === parameters.endStats) {
      return false
    }

    if (!currentUser && 'user' === parameters.endStats) {
      return false
    }

    return true
  }
)

export const select = {
  quizId,
  testMode,
  offline,
  paper,
  attempt,
  steps,
  answers,
  showFeedback,
  showBack,
  showEndConfirm,
  showStatistics,
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
  hasEndPage,
  quizEndMessage,
  quizEndNavigation,
  mandatoryQuestions,
  progressionDisplayed,
  hasMoreAttempts,
  maxAttempts,
  maxAttemptsPerDay,
  attemptsReachedMessage,
  items,
  countItems,
  answersEditable,
  isTimed,
  duration,
  quizNumbering,
  showTitles,
  showCorrectionAt,
  correctionDate,
  tags,
  questionNumbering,
  showQuestionTitles,
  showEndStats
}
