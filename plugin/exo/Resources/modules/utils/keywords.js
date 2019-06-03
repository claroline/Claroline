import {trans, transChoice} from '#/main/app/intl/translation'
import {notBlank, number, chain} from '#/main/core/validation'

import {makeId} from '#/main/core/scaffolding/id'

export const keywords = {}

/**
 * Validates a keywords collection.
 *
 * @param {Array}   collection  - the list of keywords to validate
 * @param {boolean} useScore    - if true, it validates `score` prop else it validates `expected`
 * @param {number}  minKeywords - the minimum number of items required in the collection (default: 1)
 *
 * @returns {object} an error object
 */
keywords.validate = (collection, useScore, minKeywords) => {
  let errors = {}

  // Checks all keywords have a text
  if (collection.find(keyword => notBlank(keyword.text))) {
    errors.text = trans('words_empty_text_error', {}, 'quiz')
  }

  if (useScore) {
    // Checks score for all keywords is correct
    if (undefined !== collection.find(keyword => chain(keyword.score, {}, [notBlank, number]))) {
      errors.score = trans('words_score_not_valid', {}, 'quiz')
    }

    // Checks there is at least one keyword with positive score
    if (undefined === collection.find(keyword => keyword.score > 0)) {
      errors.noValidKeyword = trans('words_no_valid_solution', {}, 'quiz')
    }
  } else {
    // Checks there is at least one expected keyword
    if (undefined === collection.find(keyword => keyword.expected)) {
      errors.noValidKeyword = trans('words_no_expected_solution', {}, 'quiz')
    }
  }

  if (!minKeywords) {
    minKeywords = 1
  }

  // Checks the number of keywords
  if (collection.length < minKeywords) {
    errors.count = transChoice('words_count_answers_error', minKeywords, {count: minKeywords}, 'quiz')
  }

  // Checks there is no duplicate keywords
  if (keywords.hasDuplicates(collection)) {
    errors.duplicate = trans('words_duplicate_answers', {}, 'quiz')
  }

  return errors
}

/**
 * Checks if a keywords collection has duplicated items (based on text and caseSensitive).
 *
 * @param {Array} keywords - the list of keywords to check
 *
 * @returns {boolean} whether there are duplicates or not
 */
keywords.hasDuplicates = (keywords) => {
  let hasDuplicates = false
  keywords.forEach(keyword => {
    let count = 0
    keywords.forEach(check => {
      if (keyword.text === check.text && keyword.caseSensitive === check.caseSensitive) {
        count++
      }
    })
    if (count > 1) hasDuplicates = true
  })

  return hasDuplicates
}

/**
 * Creates a new keyword object.
 *
 * @return {object} the new keyword
 */
keywords.createNew = () => ({
  _id: makeId(),
  text: '',
  caseSensitive: false,
  score: 1,
  feedback: ''
})
