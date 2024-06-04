import {createSelector} from 'reselect'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {selectors as formSelectors} from '#/main/app/content/form'
import {selectors as resourceSelectors} from '#/main/core/resource'

import {constants} from '#/plugin/lesson/resources/lesson/constants'

const lesson = (state) => formSelectors.value(formSelectors.form(state, resourceSelectors.EDITOR_NAME), 'resource', {})
const chapters = (state) => formSelectors.value(formSelectors.form(state, resourceSelectors.EDITOR_NAME), 'chapters', [])

const numbering = createSelector(
  [lesson],
  (lesson) => get(lesson, 'display.numbering')
)

const hasCustomNumbering = createSelector(
  [numbering],
  (numbering) => numbering === constants.NUMBERING_CUSTOM
)

const hasInternalNotes = (state) => hasPermission('view_internal_notes', resourceSelectors.resourceNode(state))

export const selectors = {
  lesson,
  chapters,
  numbering,
  hasCustomNumbering,
  hasInternalNotes
}
