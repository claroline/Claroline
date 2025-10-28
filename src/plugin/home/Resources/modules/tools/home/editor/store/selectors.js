import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as baseSelectors} from '#/main/core/tool/editor/store/selectors'

const FORM_NAME = baseSelectors.STORE_NAME

const formData = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const tabs = createSelector(
  [formData],
  (formData) => formData.tabs || []
)

const errors = (state) => formSelectors.errors(formSelectors.form(state, FORM_NAME))

export const selectors = {
  FORM_NAME,

  formData,
  tabs,
  errors
}
