import {createSelector} from 'reselect'

import {selectors as directorySelectors} from '#/main/core/resources/directory/store/selectors'

const LIST_NAME = directorySelectors.STORE_NAME+'.resources'

const directory = createSelector(
  [directorySelectors.resource],
  (resource) => resource.directory
)

const listConfiguration = createSelector(
  [directory],
  (directory) => directory.list || {}
)

export const selectors = {
  LIST_NAME,

  directory,
  listConfiguration
}
