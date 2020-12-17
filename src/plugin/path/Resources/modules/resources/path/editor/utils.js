import {trans} from '#/main/app/intl/translation'

import {flattenSteps} from '#/plugin/path/resources/path/utils'

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

function getStepTitle(steps, parent) {
  let title
  if (!parent) {
    title = `${trans('step', {}, 'path')} ${steps.length + 1}`
  } else {
    const parentPath = getStepPath(parent.id, steps)
    title = `${trans('step', {}, 'path')} ${parentPath.map(i => i+1).join('.')}.${parent.children ? parent.children.length + 1 : 1}`
  }

  return title
}

/**
 * Checks if a slug is unique and generates an unique one if not.
 *
 * @param steps
 * @param desiredSlug
 */
function getStepSlug(steps, desiredSlug) {
  const flatSteps = flattenSteps(steps)

  if (-1 === flatSteps.findIndex(step => step.slug === desiredSlug)) {
    // slug is free
    return desiredSlug
  }

  let i = 1
  let newSlug = desiredSlug+'-'+i
  while (-1 !== flatSteps.findIndex(step => step.slug === newSlug)) {
    newSlug = desiredSlug+'-'+i
    i = i+1
  }

  return newSlug
}

export {
  getFormDataPart,
  getStepPath,
  getStepParent,
  getStepTitle,
  getStepSlug
}
