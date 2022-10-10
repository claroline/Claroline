import {trans} from '#/main/app/intl/translation'
import {stripDiacritics} from '#/main/core/scaffolding/text'

export const utils = {}

utils.setEditorHtml = (text, holes, solutions, hasExpectedAnswers = true) => {
  holes.forEach(hole => {
    const solution = utils.getHoleSolution(hole, solutions)
    const regex = new RegExp(`(\\[\\[${solution.holeId}\\]\\])`, 'gi')

    text = text.replace(regex, utils.makeTinyHtml(hole, solution, hasExpectedAnswers))
  })

  return text
}

utils.makeTinyHtml = (hole, solution, hasExpectedAnswers = true) => {
  let input = `<span class="cloze-hole answer-item" data-hole-id="${solution.holeId}" contentEditable="false">`

  if (hole.choices) {
    input += getSelectInput(hole, solution, hasExpectedAnswers)
  } else {
    input += getTextInput(hole, solution)
  }

  input += getEditButtons(solution)
  input += '</span>'

  return input
}

/**
 * Replaces holes HTML with ID placeholders in the cloze text.
 *
 * @param {string} text
 *
 * @returns {string}
 */
utils.getTextWithPlacerHoldersFromHtml = (text) => {
  const tmp = document.createElement('div')
  tmp.innerHTML = text

  tmp
    .querySelectorAll('.cloze-hole')
    .forEach(hole => {
      hole.parentNode.replaceChild(document.createTextNode(`[[${hole.dataset.holeId}]]`), hole)
    })

  return tmp.innerHTML
}

/**
 * Get HTML for select hole.
 *
 * @param {object} hole
 * @param {object} solution
 *
 * @return {string}
 */
function getSelectInput(hole, solution, hasExpectedAnswers = true) {
  const bestAnswer = utils.getBestAnswer(solution.answers)

  let input = `<select class="form-control input-sm" data-hole-id="${solution.holeId}">`

  if (hasExpectedAnswers) {
    // create correct answers group
    input += `<optgroup label="${trans('hole_correct_answers', {}, 'quiz')}">`
    solution.answers.filter(answer => 0 < answer.score).map(correctAnswer => {
      input += '<option'
      if (bestAnswer && bestAnswer.text === correctAnswer.text) {
        input += ' selected="true"'
      }
      input += '>'
      input += correctAnswer.text
      input += '</option>'
    })
    input += '</optgroup>'

    const incorrectAnswers = solution.answers.filter(answer => 0 >= answer.score)
    if (0 !== incorrectAnswers.length) {
      input += `<optgroup label="${trans('hole_incorrect_answers', {}, 'quiz')}">`
      incorrectAnswers.map(incorrectAnswer => {
        input += '<option>'
        input += incorrectAnswer.text
        input += '</option>'
      })
      input += '</optgroup>'
    }
  } else {
    // create answers group
    input += `<optgroup label="${trans('answers', {}, 'quiz')}">`
    solution.answers.map(answer => {
      input += '<option'
      if (bestAnswer && bestAnswer.text === answer.text) {
        input += ' selected="true"'
      }
      input += '>'
      input += answer.text
      input += '</option>'
    })
    input += '</optgroup>'
  }

  input += '</select>'

  return input
}

/**
 * Get HTML for text hole.
 *
 * @param {object} hole
 * @param {object} solution
 *
 * @return {string}
 */
function getTextInput(hole, solution) {
  const bestAnswer = utils.getBestAnswer(solution.answers)

  return `
    <input
      class="form-control input-sm"
      data-hole-id="${hole.id}"
      type="text"
      disabled="true"
      size="${hole.size}"
      value="${bestAnswer ? bestAnswer.text : ''}"
    />
  `
}

/**
 * Get edit buttons for a Hole.
 * NB : the &nbsp; inside icons is required to avoid TinyMCE to remove it.
 *
 * @param {object} solution
 *
 * @returns {string}
 */
function getEditButtons(solution) {
  return `
    <button
      type="button"
      class="btn btn-link default edit-hole-btn"
      data-hole-id="${solution.holeId}"
    >
      <span class="fa fa-fw fa-pencil edit-hole-btn-icon" data-hole-id="${solution.holeId}"></span>
    </button>
    <button
      type="button"
      class="btn btn-link default delete-hole-btn"
      data-hole-id="${solution.holeId}"
    >
      <span class="fa fa-fw fa-trash-o delete-hole-btn-icon" data-hole-id="${solution.holeId}"></span>
    </button>
  `
}

utils.getGuidLength = () => {
  return 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'.length
}

utils.replaceBetween = (text, start, end, what) => {
  return text.substring(0, start) + what + text.substring(end)
}

//splitting stuff and whatever
utils.split = (text, holes, solutions) => {
  const split = utils.getTextElements(text, holes, solutions)
  //now we can split the text accordingly
  //This is a big mess of wtf computations but I swear it gives the correct result !
  let currentPosition = 0
  let prevPos = 0
  let prevWordLength = 0

  split.forEach(el => {
    el.text = text.substr(0, el.position - currentPosition)
    //now we trim the text
    text = text.substr(el.position + utils.getGuidLength() + 4  - currentPosition)
    currentPosition += (el.position + utils.getGuidLength() + 4  - prevPos - prevWordLength)
    prevPos = el.position
    prevWordLength = utils.getGuidLength() + 4
  })

  //I want to remember the last element of the text so I add it aswell to the array
  split.push({
    word: '#endoftext#',
    position: null,
    text,
    score: null,
    holeId: null
  })

  return split
}

/**
 * Searches Holes in text and returns an ordered array of Holes
 * based on their position in text.
 *
 * @param text
 * @param holes
 *
 * @returns {Array}
 */
utils.getTextElements = (text, holes) => {
  const data = []

  holes.forEach((hole) => {
    const regex = new RegExp(`(\\[\\[${hole.id}\\]\\])`, 'g')
    const position = text.search(regex)
    if (position > -1) {
      data.push({
        choices: hole.choices,
        position,
        multiple: false,
        holeId: hole.id,
        size: hole.size
      })
    }
  })

  return data.sort((a, b) => a.position - b.position)
}

utils.getKey = (holeId, answer, solutions) => {
  let key = '_others'

  solutions.forEach(s => {
    if (s.holeId === holeId) {
      s.answers.forEach(a => {
        const expected = a.caseSensitive ? a.text : a.text.toUpperCase()
        const provided = a.caseSensitive ? answer : answer.toUpperCase()

        if (expected === provided) {
          key = a.text
        }
      })
    }
  })

  return key
}

/**
 *
 * @param {object} hole
 * @param {Array}  solutions
 *
 * @return {object|undefined}
 */
utils.getHoleSolution = (hole, solutions) => solutions.find(solution => solution.holeId === hole.id)

/**
 * Gets the answer with the maximum score for a Hole.
 *
 * @param {Array} answers
 *
 * @returns {object|null}
 */
utils.getBestAnswer = (answers) => {
  let bestAnswer = null
  answers.map(answer => {
    if (!bestAnswer || (answer.score > bestAnswer.score && 0 < answer.score)) {
      bestAnswer = answer
    }
  })

  return bestAnswer
}

/**
 * Retrieves the solution associated to a given answer if any exists.
 *
 * @param {Array}  solutions
 * @param {string} answerText
 *
 * @returns {object|undefined}
 */
utils.getAnswerSolution = (solutions, answerText) => {
  if (!answerText || 0 === answerText.trim().length) {
    return undefined
  }
  answerText = answerText.trim()

  return solutions.find(answer => {
    return answer.text === answerText || // case sensitive
      (!answer.caseSensitive && stripDiacritics(answer.text).toUpperCase() === stripDiacritics(answerText).toUpperCase()) // case insensitive
  })
}

utils.findSolutionExpectedAnswer = (solution) => {
  let expected = null

  solution.answers.forEach(answer => {
    if (!expected || expected.score < answer.score) {
      expected = answer
    }
  })

  return expected
}
