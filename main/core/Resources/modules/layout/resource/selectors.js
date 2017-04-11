import {createSelector} from 'reselect'

const resourceNode = state => state.resourceNode

const meta = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.meta
)

const editable = createSelector(
  [meta],
  (meta) => meta.editable
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
