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

const editable = createSelector(
  [rights],
  (rights) => rights.current.edit
)

const published = createSelector(
  [meta],
  (meta) => meta.published
)

export const select = {
  resourceNode,
  meta,
  editable,
  published
}
