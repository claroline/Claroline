import flatten from 'lodash/flatten'
import concat from 'lodash/concat'

const dataTreeFlatten = roots => flatten(roots.map(root => flattenChildren(root)))
const flattenChildren = object => concat([object], flatten(object.children.map(child => flattenChildren(child))))

export {
  dataTreeFlatten as flatten
}
