import {notBlank, number, gteZero, chain, setIfError} from '#/main/core/validation'

import {getDefinition} from './../../items/item-types'
import {getContentDefinition} from './../../contents/content-types'

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
