import {tex} from './../../../utils/translate'
import $ from 'jquery'

export const utils = {}

utils.setEditorHtml = (text, solutions) => {
  solutions.forEach(solution => {
    const regex = new RegExp(`(\\[\\[${solution.holeId}\\]\\])`, 'gi')
    text = text.replace(regex, utils.makeTinyHtml(solution))
  })

  return text
}

utils.makeTinyHtml = (solution) => {
  let input = ''

  const bestAnswer = utils.getBestAnswer(solution.answers)

  if (solution.answers.length === 1) {
  //if one solutions
    input = `
      <span class="cloze-hole answer-item" data-hole-id="${solution.holeId}">
        <input
          class="hole-input form-control input-sm"
          data-hole-id="${solution.holeId}"
          type="text"
          disabled="true"
          value="${bestAnswer ? bestAnswer.text : ''}"
        />
        ${getEditButtons(solution)}
      </span>
    `
  } else {
    input = `
      <span class="cloze-hole answer-item" data-hole-id="${solution.holeId}">
        <select
          class="hole-input form-control input-sm"
          data-hole-id="${solution.holeId}"
        >
          <option>${bestAnswer ? bestAnswer.text : tex('please_choose')}</option>
        </select>
        ${getEditButtons(solution)}
      </span>
    `
  }
  return input
}

utils.getTextWithPlacerHoldersFromHtml = (text) => {
  const tmp = document.createElement('div')
  tmp.innerHTML = text

  $(tmp).find('.cloze-hole').each(function () {
    let id = $(this).attr('data-hole-id')
    $(this).replaceWith(`[[${id}]]`)
  })

  //we remove the last tag because it's added automatically
  const regex = new RegExp('(<\/[^<>]*>$)', 'gi')
  tmp.innerHTML = tmp.innerHTML.replace(regex, '')

  return $(tmp).html()
}

function getEditButtons(solution) {
  return `
    <button 
      type="button" 
      class="btn btn-link-default edit-hole-btn"
      data-hole-id="${solution.holeId}"
    >
      <span class="fa fa-fw fa-pencil edit-hole-btn-icon" data-hole-id="${solution.holeId}">&nbsp;</span>
    </button>
    <button 
      type="button" 
      class="btn btn-link-default delete-hole-btn"
      data-hole-id="${solution.holeId}"
    >
      <span class="fa fa-fw fa-trash-o delete-hole-btn-icon" data-hole-id="${solution.holeId}">&nbsp;</span>
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

  //I want to rember the last element of the text so I add it aswell to the array
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

utils.getBestAnswer = (answers) => {
  let bestAnswer = null
  answers.map(answer => {
    if (!bestAnswer || (answer.score > bestAnswer && 0 < answer.score)) {
      bestAnswer = answer
    }
  })

  return bestAnswer
}