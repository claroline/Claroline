import {notBlank, notEmpty, number, gteZero, gtZero, chain, setIfError} from '#/main/core/validation'
import {tex} from '#/main/core/translation'

import {getDefinition} from '#/plugin/exo/items/item-types'
import {getContentDefinition} from '#/plugin/exo/contents/content-types'

import {
  QUIZ_PICKING_DEFAULT,
  QUIZ_PICKING_TAGS
} from '#/plugin/exo/quiz/enums'

function validateQuiz(quiz) {
  const errors = {}

  // validates Quiz parameters
  const parameters = quiz.parameters
  const paramErrors = {}

  setIfError(paramErrors, 'duration', chain(parameters.duration, {}, [notBlank, number, gteZero]))
  setIfError(paramErrors, 'maxAttempts', chain(parameters.maxAttempts, {}, [notBlank, number, gteZero]))
  setIfError(paramErrors, 'maxAttemptsPerDay', chain(parameters.maxAttemptsPerDay, {}, [notBlank, number, gteZero, (value) => {
    if (value > parameters.maxAttempts) return tex('must_be_less_than_max_attempts')
  }]))
  setIfError(paramErrors, 'maxPapers', chain(parameters.maxPapers, {}, [notBlank, number, gteZero, (value) => {
    if (value < parameters.maxAttempts && value !== 0) return tex('must_be_more_than_max_attempts')
  }]))

  if (Object.keys(paramErrors).length > 0) {
    errors.parameters = paramErrors
  }

  // validates Quiz picking
  const picking = quiz.picking
  const pickingErrors = {}

  switch (picking.type) {
    case QUIZ_PICKING_TAGS:
      setIfError(pickingErrors, 'pick', chain(picking.pick, {}, [notEmpty]))
      setIfError(pickingErrors, 'pageSize', chain(picking.pageSize, {}, [notBlank, number, gtZero]))
      break
    case QUIZ_PICKING_DEFAULT:
    default:
      setIfError(pickingErrors, 'pick', chain(picking.pick, {}, [notBlank, number, gteZero]))
      break
  }

  if (Object.keys(pickingErrors).length > 0) {
    errors.picking = pickingErrors
  }

  return errors
}

function validateStep(step) {
  const errors = {}

  setIfError(
    errors,
    'parameters.maxAttempts',
    chain(step.parameters.maxAttempts, {}, [notBlank, number, gteZero])
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

  setIfError(errors, 'content', notBlank(item.content, {isHtml: true}))
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
