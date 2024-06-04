import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as editorSelectors} from '#/main/core/resource/editor'
import {constants} from '#/plugin/path//resources/path/constants'

const steps = createSelector(
  [editorSelectors.resource],
  (path) => path.steps || []
)

const numbering = createSelector(
  [editorSelectors.resource],
  (path) => get(path, 'display.numbering')
)

const hasCustomNumbering = createSelector(
  [numbering],
  (numbering) => numbering === constants.NUMBERING_CUSTOM
)

export const selectors = {
  steps,
  numbering,
  hasCustomNumbering
}
