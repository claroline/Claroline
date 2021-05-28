import classes from 'classnames'
import {displayDate} from '#/main/app/intl/date'

function getKeywordRegex(keyword, caseSensitive = false) {
  const escapedKeyword = keyword.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&')

  const pattern = '(?:\\W|^)('+escapedKeyword+')(?:\\W|$)'

  let regexFlag = ''
  if (!caseSensitive) {
    regexFlag += 'i'
  }

  return new RegExp(pattern, regexFlag)
}

function containsKeyword(keyword, caseSensitive, text = '') {
  return getKeywordRegex(keyword, caseSensitive).test(text)
}

function findSolutions(text, solutions) {
  if (!text) {
    return []
  }

  return solutions.filter(solution => containsKeyword(solution.text, solution.caseSensitive, text))
}

function highlight(text, contentType, solutions, hasExpectedAnswers) {
  let highlightedText = text
  solutions.map(solution => {
    const status = classes({
      'correct-answer': hasExpectedAnswers && solution.score > 0,
      'incorrect-answer': hasExpectedAnswers && solution.score <= 0,
      'selected-answer': !hasExpectedAnswers
    })

    let replacer = `<strong class="${status}">`
    if (hasExpectedAnswers) {
      const icon = classes('fa fa-fw', {
        'fa-check': hasExpectedAnswers && solution.score > 0,
        'fa-times': hasExpectedAnswers && solution.score <= 0
      })

      replacer += `<span class="${icon}"></span> ` // final whitespace is not a typo ;)
    }

    replacer += 'date' === contentType ? displayDate(solution.text) : solution.text
    replacer += '</strong>'

    highlightedText = highlightedText.replace(getKeywordRegex(solution.text, solution.caseSensitive), ' ' + replacer + ' ')
  })

  return highlightedText
}

export const utils = {
  containsKeyword,
  findSolutions,
  highlight
}
