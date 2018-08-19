import {createSelector} from 'reselect'

import {findInTree} from '#/plugin/wiki/resources/wiki/utils'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const wiki = createSelector(
  [resource],
  (resource) => resource.wiki
)

const sections = createSelector(
  [resource],
  (resource) => resource.sections
)

const history = createSelector(
  [resource],
  (resource) => resource.history
)

const sectionsTree = createSelector(
  [sections],
  (sections) => sections.tree
)

const currentSection = createSelector(
  [resource],
  (resource) => resource.history.currentSection
)

const currentVersion = createSelector(
  [resource],
  (resource) => resource.history.currentVersion
)

const compareSet = createSelector(
  [resource],
  (resource) => resource.history.compareSet
)

const mode = createSelector(
  [wiki],
  (wiki) => wiki.mode
)

const section = (state, id) => findInTree(state[STORE_NAME].sections.tree, id, 'children', 'id')

export const selectors = {
  STORE_NAME,
  resource,
  wiki,
  history,
  currentSection,
  sections,
  compareSet,
  currentVersion,
  mode,
  section,
  sectionsTree
}
