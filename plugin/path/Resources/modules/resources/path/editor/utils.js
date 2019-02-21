
function getFormDataPart(id, steps) {
  const stepPath = getStepPath(id, steps)
  let formDataPart = `steps[${stepPath[0]}]`

  for (let i = 1; i < stepPath.length; ++i) {
    formDataPart += `.children[${stepPath[i]}]`
  }

  return formDataPart
}

function getStepPath(id, steps, level = 0, indexes = []) {
  const index = steps.findIndex(s => s.id === id)

  if (index > -1) {
    indexes[level] = index
    indexes.splice(level + 1)

    return indexes
  } else {
    for (let key = 0; key < steps.length; ++key) {
      if (steps[key].children.length > 0) {
        indexes[level] = key
        const stepPath = getStepPath(id, steps[key].children, level + 1, indexes)

        if (stepPath) {
          return stepPath
        }
      }
    }

    return null
  }
}

function getStepParent(id, steps) {
  const stepPath = getStepPath(id, steps)

  // remove current
  stepPath.pop()

  if (0 !== stepPath.length) {
    let parent = steps[stepPath[0]]
    for (let i = 1; i < stepPath.length; i++) {
      parent = parent.children[stepPath[i]]
    }

    return parent
  }

  return null
}

export {
  getFormDataPart,
  getStepPath,
  getStepParent
}
