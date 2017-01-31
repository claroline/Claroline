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

utils.getAnswerClassForSolution = (solution, answers) => {
  return utils.isSolutionChecked(solution, answers) ?
    solution.score > 0 ? 'bg-success text-success' : 'bg-danger text-danger' : ''
}
