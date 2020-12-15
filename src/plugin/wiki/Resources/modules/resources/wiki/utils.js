import cloneDeep from 'lodash/cloneDeep'
import find from 'lodash/find'
import set from 'lodash/set'

const flattenItems = (items, key) => {
  return items.reduce((flattened, item) => {
    flattened.push(item)
    if (Array.isArray(item[key])) {
      flattened = flattened.concat(flattenItems(item[key], key))
    }
    return flattened
  }, [])
}

const updatePropInTree = (items, key, property, value) => {
  items.forEach(item => {
    if (item['id'] === key) {
      set(item, property, value)
    } else if (Array.isArray(item['children']) && item['children'].length > 0) {
      updatePropInTree(item['children'], key, property, value)
    }
  })
}

const appendChildToTreeNode = (items, parentId, child) => {
  items.forEach(item => {
    if (item['id'] === parentId) {
      if (!Array.isArray(item['children'])) {
        item['children'] = []
      }
      item['children'].push(child)
    } else if (Array.isArray(item['children']) && item['children'].length > 0) {
      appendChildToTreeNode(item['children'], parentId, child)
    }
  })
}

const deleteNodeFromTree = (items, sectionId, children) => {
  items.forEach((item, idx) => {
    if (item['id'] === sectionId) {
      let replacement = []
      if (!children && Array.isArray(items[idx]['children'])) {
        replacement = items[idx]['children']
      }
      items.splice(idx, 1, ...replacement)
    } else if (Array.isArray(item['children']) && item['children'].length > 0) {
      deleteNodeFromTree(item['children'], sectionId, children)
    }
  })
}

const buildFlattenedSectionChoices = (items, sectionId, num) => {
  let flattenedSections = {}
  items.forEach((item, idx) => {
    if (item['id'] !== sectionId) {
      let tmpNum = num.concat([idx + 1])
      flattenedSections[item['id']] =`${tmpNum.join('.')} ${item['activeContribution']['title']}`
      if (item['children'] && Array.isArray(item['children']) && item['children'].length > 0) {
        flattenedSections = Object.assign(flattenedSections, buildFlattenedSectionChoices(item['children'], sectionId, tmpNum))
      }
    }
  })

  return flattenedSections
}

export const findInTree = (tree, id, childrenProperty = 'children', idProperty = 'id') => {
  return find(flattenItems(Array.isArray(tree) ? tree : [tree], childrenProperty), [idProperty, id])
}

export const updateInTree = (tree, id, property, value) => {
  const copy = cloneDeep(tree)
  updatePropInTree(Array.isArray(copy) ? copy : [copy], id, property, value)

  return copy
}

export const appendChildToTree = (tree, parentId, child) => {
  const copy = cloneDeep(tree)
  appendChildToTreeNode(Array.isArray(copy) ? copy : [copy], parentId, child)

  return copy
}

export const deleteFromTree = (tree, sectionId, children) => {
  const copy = cloneDeep(tree)
  deleteNodeFromTree(Array.isArray(copy) ? copy : [copy], sectionId, children)
  
  return copy
}

export const buildSectionMoveChoices = (tree, sectionId = null) => {
  if (sectionId !== null && tree.id !== sectionId && tree['children'] && Array.isArray(tree['children']) && tree['children'].length > 0) {
    return buildFlattenedSectionChoices(tree['children'], sectionId, [])
  }

  return {}
}

export const buildDataPart = path => {
  let dataPart = ''
  if (!Array.isArray(path) || path.length === 0) {
    return dataPart
  }
  for (let part of path) {
    dataPart += `${dataPart.length !== 0 ? '.' : ''}children[${part - 1}]`
  }
  
  return dataPart
}