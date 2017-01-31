// flattens raw quiz data
export function normalize(rawQuiz) {
  let items = {}

  const steps = rawQuiz.steps.reduce((stepAcc, step) => {
    items = step.items.reduce((itemAcc, item) => {
      itemAcc[item.id] = item

      return itemAcc
    }, items)

    stepAcc[step.id] = Object.assign({}, step)
    stepAcc[step.id].items = step.items.map(item => item.id)

    return stepAcc
  }, {})

  return {
    quiz: {
      id: rawQuiz.id,
      title: rawQuiz.title,
      description: rawQuiz.description,
      meta: rawQuiz.meta,
      parameters: rawQuiz.parameters,
      steps: rawQuiz.steps.map(step => step.id)
    },
    steps,
    items
  }
}

// unflattens flat quiz data
export function denormalize(quiz, steps, items) {

  let rawQuizSteps = []
  quiz.steps.forEach(stepId => {
    let step = Object.assign({}, steps[stepId])
    let stepItems = []
    step.items.forEach(itemId => {
      let item = Object.assign({}, items[itemId])
      stepItems.push(item)
    })
    step.items = stepItems
    rawQuizSteps.push(step)
  })

  return {
    id: quiz.id,
    title: quiz.title,
    description: quiz.description,
    meta: quiz.meta,
    parameters: quiz.parameters,
    steps: rawQuizSteps
  }
}
