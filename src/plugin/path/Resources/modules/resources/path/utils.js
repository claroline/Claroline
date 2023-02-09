import {constants} from '#/plugin/path/resources/path/constants'

/**
 * Flattens a tree of steps into a one-level array.
 *
 * @param {Array}  steps
 */
function flattenSteps(steps) {
  function flatten(step, parent = null) {
    const children = step.children
    const flatStep = Object.assign({}, step)

    delete flatStep.children
    if (parent) {
      flatStep.parent = {
        id: parent.id,
        title: parent.title
      }
    }

    let flattened = [flatStep]

    if (children) {
      children.map((child) => {
        flattened = flattened.concat(flatten(child, flatStep))
      })
    }

    return flattened
  }

  return steps.reduce((acc, step) => acc.concat(flatten(step)), [])
}

/**
 * Get the display numbering of a step.
 *
 * @param {string} type
 * @param {Array} steps
 * @param {Step.propTypes} step
 *
 * @return {string}
 */
function getNumbering(type, steps, step) {
  function buildPath(steps, step) {
    let stepPath = []

    steps.map((s, i) => {
      if (s.id === step.id) {
        stepPath.push(i) // add current step to the path
      } else if (s.children && 0 !== s.children.length) {
        const subPath = buildPath(s.children, step)
        if (0 !== subPath.length) {
          stepPath = stepPath.concat([i], subPath)
        }
      }
    })

    return stepPath
  }

  switch (type) {
    /**
     * The numbering label is a number.
     */
    case constants.NUMBERING_NUMERIC:
      return '' + buildPath(steps, step)
        // make numbering start to 1 for users
        .map(i => i + 1)
        .join('.')

    /**
     * The numbering label is a letter.
     */
    case constants.NUMBERING_LITERAL:
      return buildPath(steps, step)
        // get correct letter
        .map(i => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[i])
        .join('.')

    /**
     * The numbering label is specified by each step.
     */
    case constants.NUMBERING_CUSTOM:
      return step.display.numbering

    /**
     * The numbering feature is disabled.
     */
    case constants.NUMBERING_NONE:
    default:
      return ''
  }
}

export {
  flattenSteps,
  getNumbering
}
