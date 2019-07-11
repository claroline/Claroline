import {createSelector} from 'reselect'

import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

const STORE_NAME = 'file'

const resource = (state) => state[STORE_NAME]

const file = createSelector(
  [resource],
  (resource) => resource.file
)

const url = createSelector(
  [file],
  (file) => file.url
)

const mimeType = resourceSelectors.mimeType

export const selectors = {
  STORE_NAME,
  resource,
  file,
  url,
  mimeType
}
