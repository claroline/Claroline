// flattens raw paper data
export function normalize(rawPaper) {
  let answers = {}

  if (rawPaper.answers) {
    answers = normalizeAnswers(rawPaper.answers)
  }

  const paper = Object.assign({}, rawPaper)
  paper.answers = paper.answers ? paper.answers.map(answer => answer.questionId) : []

  return {
    paper,
    answers
  }
}

export function normalizeAnswers(rawAnswers = []) {
  let answers = {}

  if (0 !== rawAnswers.length) {
    answers = rawAnswers.reduce((answerAcc, answer) => {
      answerAcc[answer.questionId] = Object.assign({}, answer)

      return answerAcc
    }, {})
  }

  return answers
}

export function denormalizeAnswers(answers = {}) {
  return Object.keys(answers).map(key => answers[key])
}

export function denormalize(paper, answers) {
  return Object.assign({}, paper, {answers: denormalizeAnswers(answers)})
}
