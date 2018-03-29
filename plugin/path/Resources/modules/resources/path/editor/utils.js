import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'

function getFormDataPart(id, steps) {
  const stepPath = getStepPath(id, steps, 0, [])
  let formDataPart = `steps[${stepPath[0]}]`

  for (let i = 1; i < stepPath.length; ++i) {
    formDataPart += `.children[${stepPath[i]}]`
  }

  return formDataPart
}

function getStepPath(id, steps, level, indexes) {
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

function manageInheritedResources(step, id, resource, lvl) {
  const index = step.inheritedResources.findIndex(ir => ir.sourceUuid === id)

  if (index === -1 && resource) {
    step.inheritedResources.push({
      id: makeId(),
      resource: resource,
      lvl: lvl,
      sourceUuid: id
    })
  } else if (index > -1 && !resource) {
    step.inheritedResources.splice(index, 1)
  }
  step.children.forEach(s => manageInheritedResources(s, id, resource, lvl))
}

function generateCopy(step, lvl, inherited) {
  step.id = makeId()
  step.title += ` (${trans('step_copy', {}, 'path')})`
  const irToRemove = []

  step.inheritedResources.forEach((ir, index) => {
    if (inherited[ir.sourceUuid]) {
      ir.id = makeId()
      ir.lvl = inherited[ir.sourceUuid].lvl
      ir.sourceUuid = inherited[ir.sourceUuid].id
    } else {
      irToRemove.unshift(index)
    }
  })
  irToRemove.forEach(index => step.inheritedResources.splice(index, 1))

  step.secondaryResources.forEach(sr => {
    const newId = makeId()

    if (sr.inheritanceEnabled) {
      inherited[sr.id] = {
        id: newId,
        lvl: lvl
      }
    }
    sr.id = newId
  })

  step.children.forEach(s => generateCopy(s, lvl + 1, inherited))
}

function updateCopyBeforeAdding(step, lvl, inheritedResources) {
  step.inheritedResources.forEach(ir => ir.lvl += lvl)
  inheritedResources.forEach(ir => step.inheritedResources.push({
    id: makeId(),
    lvl: ir.lvl,
    resource: ir.resource,
    sourceUuid: ir.sourceUuid
  }))

  step.children.forEach(s => updateCopyBeforeAdding(s, lvl, inheritedResources))
}

export {
  getFormDataPart,
  getStepPath,
  manageInheritedResources,
  generateCopy,
  updateCopyBeforeAdding
}
