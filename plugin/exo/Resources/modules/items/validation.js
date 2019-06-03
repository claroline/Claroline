import merge from 'lodash/merge'

import {notBlank, number, gteZero, chain} from '#/main/core/validation'

import {getItem} from '#/plugin/exo/items'

/**
 * Validates an answerable item.
 *
 * @param {object}   item            - the item to validate.
 * @param {function} customValidator - a custom validation function provided by the item type.
 *
 * @return {object} the list of errors of the item.
 */
function validateQuestion(item, customValidator) {
  let errors = {}

  errors.content = notBlank(item.content, {isHtml: true})

  // validate hints
  let hintErrors = []
  if (item.hints) {
    hintErrors = item.hints.map(hint => {
      const hErrors = []
      const valueError = chain(hint.value, {isHtml: true}, [notBlank])
      const penaltyError = chain(hint.penalty, {}, [notBlank, number, gteZero])

      if (valueError) {
        hErrors.push(valueError)
      }

      if (penaltyError) {
        hErrors.push(penaltyError)
      }

      return hErrors
    })
  }

  errors.hints = hintErrors

  // validate objects
  let objectErrors = []
  if (item.objects) {
    objectErrors = item.objects.map(o => validate(o))
  }

  errors.objects = objectErrors

  // validate custom item props
  if (customValidator) {
    const customErrors = customValidator(item)
    if (customErrors) {
      errors = merge(errors, customErrors)
    }
  }

  return errors
}

/**
 * Validates a content item.
 *
 * @param {object}   item            - the item to validate.
 * @param {function} customValidator - a custom validation function provided by the item type.
 *
 * @return {object} the list of errors of the item.
 */
function validateContent(item, customValidator) {
  if (customValidator) {
    return customValidator(item)
  }
}

/**
 * Validates a quiz item.
 *
 * @param {object} item - the item to validate.
 *
 * @return {Promise} the list of errors of the item.
 */
function validate(item) {
  return getItem(item.type).then((definition) => {
    if (definition.answerable) {
      return validateQuestion(item, definition.validate)
    }

    return validateContent(item, definition.validate)
  })
}

export {
  validate
}
