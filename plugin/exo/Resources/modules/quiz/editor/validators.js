import {notBlank, number, gteZero, chain, setIfError} from '#/main/core/validation'
import {tex} from '#/main/core/translation'
import {getDefinition} from './../../items/item-types'
import {getContentDefinition} from './../../contents/content-types'

function validateQuiz(quiz) {
  const parameters = quiz.parameters
  const errors = {}
  const paramErrors = {}

  setIfError(paramErrors, 'pick', chain(parameters.pick, [notBlank, number, gteZero]))
  setIfError(paramErrors, 'duration', chain(parameters.duration, [notBlank, number, gteZero]))
  setIfError(paramErrors, 'maxAttempts', chain(parameters.maxAttempts, [notBlank, number, gteZero]))
  setIfError(paramErrors, 'maxAttemptsPerDay', chain(parameters.maxAttemptsPerDay, [notBlank, number, gteZero, (value) => {
    if (value > parameters.maxAttempts) return tex('must_be_less_than_max_attempts')
  }]))
  setIfError(paramErrors, 'maxPapers', chain(parameters.maxPapers, [notBlank, number, gteZero, (value) => {
    if (value < parameters.maxAttempts) return tex('must_be_more_than_max_attempts')
  }]))

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

function validateContentItem(contentItem) {
  const errors = {}
  const subErrors = getContentDefinition(contentItem.type).editor.validate(contentItem)

  return Object.assign(errors, subErrors)
}

function validateBaseItem(item) {
  const errors = {}

  setIfError(errors, 'content', notBlank(item.content, true))
  const objectsErrors = validateItemObjects(item)

  return Object.assign(errors, objectsErrors)
}

function validateItemObjects(item) {
  let errors = {}

  if (item.objects) {
    item.objects.forEach(o => errors = Object.assign(errors, validateContentItem(o)))
  }

  return errors
}

export default {
  quiz: validateQuiz,
  step: validateStep,
  item: validateItem,
  contentItem: validateContentItem
}
