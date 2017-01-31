import {notBlank, number, gteZero, chain, setIfError} from './../../utils/validate'
import {getDefinition} from './../../items/item-types'

function validateQuiz(quiz) {
  const parameters = quiz.parameters
  const errors = {}
  const paramErrors = {}

  setIfError(errors, 'title', notBlank(quiz.title))
  setIfError(paramErrors, 'pick', chain(parameters.pick, [notBlank, number, gteZero]))
  setIfError(paramErrors, 'duration', chain(parameters.duration, [notBlank, number, gteZero]))
  setIfError(paramErrors, 'maxAttempts', chain(parameters.maxAttempts, [notBlank, number, gteZero]))

  if (Object.keys(paramErrors).length > 0) {
    errors.parameters = paramErrors
  }

  return errors
}

function validateStep(step) {
  const errors = {}

  setIfError(
    errors,
    'parameters.maxAttempts',
    chain(step.parameters.maxAttempts, [notBlank, number, gteZero])
  )

  return errors
}

function validateItem(item) {
  const errors = validateBaseItem(item)
  const subErrors = getDefinition(item.type).editor.validate(item)

  return Object.assign(errors, subErrors)
}

function validateBaseItem(item) {
  const errors = {}

  setIfError(errors, 'content', notBlank(item.content, true))

  return errors
}

export default {
  quiz: validateQuiz,
  step: validateStep,
  item: validateItem
}
