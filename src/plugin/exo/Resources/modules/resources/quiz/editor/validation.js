import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {notBlank, notEmpty, number, gteZero, gtZero, chainSync} from '#/main/app/data/types/validators'
import {trans, tval} from '#/main/app/intl/translation'

import {validate as validateItem} from '#/plugin/exo/items/validation'
import {constants} from '#/plugin/exo/resources/quiz/constants'

/**
 * Validates a quiz step.
 *
 * @param {object} step - the step to validate.
 *
 * @return {Promise} the list of errors in the step (using same structure than data for form rendering)
 */
function validateStep(step) {
  const errors = {}

  // validates parameters
  const parameters = step.parameters
  const paramErrors = {}

  paramErrors.maxAttempts = chainSync(parameters.maxAttempts, {}, [notBlank, number, gteZero])

  if (!isEmpty(paramErrors)) {
    errors.parameters = paramErrors
  }

  // validates items
  const items = step.items || []

  return Promise
    .all(
      items.map(item => validateItem(item))
    )
    .then(itemErrors => merge(errors, {items: itemErrors}))
}

/**
 * Validates a quiz.
 *
 * @param {object} quiz - the quiz to validate.
 *
 * @return {Promise} the list of errors in the quiz (using same structure than data for form rendering)
 */
function validate(quiz) {
  const errors = {}

  // validates Quiz parameters
  const parameters = quiz.parameters
  const paramErrors = {}

  paramErrors.duration = chainSync(parameters.duration, {}, [notBlank, number, gteZero])
  paramErrors.maxAttempts = chainSync(parameters.maxAttempts, {}, [notBlank, number, gteZero])
  paramErrors.maxAttemptsPerDay = chainSync(parameters.maxAttemptsPerDay, {}, [notBlank, number, gteZero, (value) => {
    if (parameters.maxAttempts && value > parameters.maxAttempts) {
      return trans('must_be_less_than_max_attempts', {}, 'quiz')
    }
  }])
  paramErrors.maxPapers = chainSync(parameters.maxPapers, {}, [notBlank, number, gteZero, (value) => {
    if (parameters.maxAttempts && value < parameters.maxAttempts && value !== 0) {
      return trans('must_be_more_than_max_attempts', {}, 'quiz')
    }
  }])

  if (!isEmpty(paramErrors)) {
    errors.parameters = paramErrors
  }

  // validates Quiz score
  const scoreErrors = {}
  if (isEmpty(quiz.score) || isEmpty(quiz.score.type)) {
    scoreErrors.type = tval('This value should not be blank.')
  }

  if (!isEmpty(scoreErrors)) {
    errors.score = scoreErrors
  }

  // validates Quiz picking
  const picking = quiz.picking
  const pickingErrors = {}

  switch (picking.type) {
    case constants.QUIZ_PICKING_TAGS:
      pickingErrors.pageSize = chainSync(picking.pageSize, {}, [notBlank, number, gtZero])
      pickingErrors.pick = chainSync(picking.pick, {}, [notEmpty, (value = []) => {
        return value.map((toPick = []) => {
          if (!toPick[0] || !toPick[1]) {
            return tval('This value should not be blank.')
          }
        })
      }])
      break

    case constants.QUIZ_PICKING_DEFAULT:
    default:
      pickingErrors.pick = chainSync(picking.pick, {}, [notBlank, number, gteZero])
      break
  }

  if (!isEmpty(pickingErrors)) {
    errors.picking = pickingErrors
  }

  // validate quiz steps
  const steps = quiz.steps || []

  return Promise
    .all(
      steps.map(step => validateStep(step))
    )
    .then(stepErrors => merge(errors, {steps: stepErrors}))
}

export {
  validate
}
