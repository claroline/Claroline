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
  if (solution.answers.length === 1) {
  //if one solutions
    input = `
      <span class="cloze-input" data-hole-id="${solution.holeId}">
        <input
          style="width: auto; margin: 10px; display: inline;"
          class="hole-input form-control"
          data-hole-id="${solution.holeId}"
          type="text"
        >
        </input>
        ${getEditButtons(solution)}
      </span>
    `
  } else {
    input = `
      <span class="cloze-input" data-hole-id="${solution.holeId}">
        <select
          style="width: auto; margin: 10px; display: inline;"
          class="hole-input form-control"
          data-hole-id="${solution.holeId}"
          type="text"
        >
          <option> ${tex('please_choose')} </option>
        </select>
        ${getEditButtons(solution)}
      </span>
    `
  }
  return input
}

utils.getTextWithPlacerHoldersFromHtml = (text) =>
{
  const tmp = document.createElement('div')
  tmp.innerHTML = text

  $(tmp).find('.cloze-input').each(function () {
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
    <i style="cursor: pointer"
      class="fa fa-pencil edit-hole-btn"
      data-hole-id="${solution.holeId}"
    > &nbsp; </i>
    <i style="cursor: pointer"
      class="fa fa-trash delete-hole-btn"
      data-hole-id="${solution.holeId}"
    > &nbsp;
    </i>
  `
}

utils.getEditButtonsLength = () => {
  const solution = {
    guid: 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
  }

  return utils.makeTinyHtml(solution).length
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

utils.getSolutionForAnswer = (solution, answer) => {
  let answerText = ''
  if (typeof answer === 'string') {
    answerText = answer
  } else {
    answerText = answer.text ? answer.text: answer.answerText
  }


  if (!answerText) return null

  let foundSolution = solution.answers.find(solAnswer => solAnswer.text.toLowerCase() === answerText.toLowerCase())

  if (!foundSolution) return null

  foundSolution = foundSolution.caseSensitive ? foundSolution.text === answer.text ? foundSolution: null: foundSolution

  return foundSolution
}
