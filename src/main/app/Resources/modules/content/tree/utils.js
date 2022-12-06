import concat from 'lodash/concat'
import flatten from 'lodash/flatten'

function flattenTree(roots = []) {
  return flatten(roots.map(root => flattenChildren(root)))
}

function flattenChildren(object) {
  return concat([object], object.children ? flatten(object.children.map(child => flattenChildren(child))) : [])
}

export {
  flattenTree
}
