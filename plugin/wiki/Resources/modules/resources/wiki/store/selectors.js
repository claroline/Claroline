import {findInTree} from '#/plugin/wiki/resources/wiki/utils'

const section = (state, id) => {
  return findInTree(state.sections.tree, id, 'children', 'id')
}

export const selectors = {
  section
}