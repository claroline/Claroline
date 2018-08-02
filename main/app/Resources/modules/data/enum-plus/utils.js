import {trans} from '#/main/core/translation'
import {searchChoice} from '#/main/core/layout/select-plus/utils'

/**
 * Checks if a choice is valid
 *
 * @param choices
 * @param value
 * @return {boolean} - is choice valid or not
 */
const isChoiceValid = (choices, value, transDomain) => {
  if (!value) {
    return false
  }

  return searchChoice(choices, value, transDomain, true).length > 0
}

/**
 * Parses a displayed text and matches it to its proper choice
 *
 * @param choices
 * @param display
 * @param transDomain
 * @return {string | null} - return choice if one was found, null otherwise
 */
const parseChoice = (choices, display, transDomain) => {
  if (!display) {
    return null
  }
  let parsed = null
  let choice = searchChoice(choices, display, transDomain)
  if (choice.length === 1) {
    choice = choice[0]
    while(choice.choices.length > 0) {
      choice = choice.choices[0]
    }
    parsed = choice.value
  }

  return parsed
}

/**
 * Renders a choice to it's displayed value
 *
 * @param raw
 * @param choices
 * @param transDomain
 * @return {string} - raw if choice was not found, formatted found choice text otherwise
 */
const renderChoice = (choices, raw, transDomain) => {
  if (!raw) {
    return null
  }
  let rendered = null
  let choice = searchChoice(choices, raw, transDomain, true)
  if (choice.length === 1) {
    choice = choice[0]
    rendered = ''
    let i = 0
    while(choice.choices.length > 0) {
      rendered += `${i===0 ? '' : ' : '}${transDomain ? trans(choice.label, {}, transDomain) : choice.label}`
      choice = choice.choices[0]
      i++
    }
    rendered += `${i > 0 ? ' : ' : ''}${transDomain ? trans(choice.label, {}, transDomain) : choice.label}`
  }

  return rendered || raw
}

export {
  isChoiceValid,
  parseChoice,
  renderChoice
}