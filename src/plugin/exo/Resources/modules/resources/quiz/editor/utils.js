
function getStepSlug(steps, desiredSlug) {
  if (-1 === steps.findIndex(step => step.slug === desiredSlug)) {
    // slug is free
    return desiredSlug
  }

  let i = 1
  let newSlug = desiredSlug+'-'+i
  while (-1 !== steps.findIndex(step => step.slug === newSlug)) {
    newSlug = desiredSlug+'-'+i
    i = i+1
  }

  return newSlug
}

export {
  getStepSlug
}
