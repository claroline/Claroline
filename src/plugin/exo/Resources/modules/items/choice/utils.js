export const utils = {}

utils.getChoiceById = (choices, choiceId) => {
  return choices.find(choice => choice.id === choiceId)
}

utils.isSolutionChecked = (solution, answers) => {
  return answers ? answers.indexOf(solution.id) > -1 : false
}

utils.answerId = (id) => {
  return `${id}-your-answer`
}

utils.expectedId = (id) => {
  return `${id}-expected-answer`
}

utils.getAnswerClassForSolution = (solution, answers, hasExpectedAnswer = true) => {
  const checked = utils.isSolutionChecked(solution, answers)
  if (checked) {
    if (hasExpectedAnswer) {
      if (solution.score > 0) {
        return 'correct-answer'
      }

      return 'incorrect-answer'
    }

    return 'selected-answer'
  }

  return ''
}

utils.setChoiceTicks = (choices, multiple = false) => {
  if (multiple) {
    choices.map(choice => choice.checked = choice.score > 0)
  } else {
    let max = 0
    let maxId = null

    choices.map(choice => {
      if (choice.score > max) {
        max = choice.score
        maxId = choice.id
      }
    })

    choices.map(choice =>
      choice.checked = max > 0 && choice.id === maxId
    )
  }

  return choices
}