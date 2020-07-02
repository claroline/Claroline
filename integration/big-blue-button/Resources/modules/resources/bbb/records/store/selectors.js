import get from 'lodash/get'

import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/integration/big-blue-button/resources/bbb/store/selectors'

const recordings = createSelector(
  [baseSelectors.resource],
  (resource) => get(resource, 'recordings', [])
    .filter(r => 'deleted' !== r.state)
)

export const selectors = {
  recordings
}
