import {createSelector} from 'reselect'

import {select as formSelect} from '#/main/core/data/form/selectors'

const editorData = (state) => formSelect.data(formSelect.form(state, 'editor'))

const tabs = createSelector(
  [editorData],
  (editorData) => editorData.tabs
)

const widgets = createSelector(
  [editorData],
  (editorData) => editorData.widgets
)

export const select = {
  tabs,
  widgets
}
