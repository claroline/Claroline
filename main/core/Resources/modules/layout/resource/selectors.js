import {createSelector} from 'reselect'

const resourceNode = state => state.resourceNode

const meta = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.meta
)

const rights = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.rights
)

const currentRights = createSelector(
  [rights],
  (rights) => rights.current
)

const editable = createSelector(
  [currentRights],
  (currentRights) => currentRights.edit
)

const published = createSelector(
  [meta],
  (meta) => meta.published
)

export const select = {
  resourceNode,
  meta,
  currentRights,
  editable,
  published
}
