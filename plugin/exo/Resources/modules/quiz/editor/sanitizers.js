// todo : could be removed when we will use semantic form group
function sanitizeQuiz(propertyPath, value) {
  if (propertyPath === 'parameters.duration'
    || propertyPath === 'parameters.maxAttempts'
    || propertyPath === 'parameters.maxAttemptsPerDay'
    || propertyPath === 'parameters.maxPapers') {
    value = parseInt(value)
  }

  return value
}

// todo : could be removed when we will use semantic form group
function sanitizeStep(step) {
  if (step.parameters) {
    if (step.parameters.maxAttempts) {
      step.parameters.maxAttempts = parseInt(step.parameters.maxAttempts)
    }
    if (step.parameters.maxAttemptsPerDay) {
      step.parameters.maxAttemptsPerDay = parseInt(step.parameters.maxAttemptsPerDay)
    }
  }
  return step
}

export default {
  quiz: sanitizeQuiz,
  step: sanitizeStep
}
