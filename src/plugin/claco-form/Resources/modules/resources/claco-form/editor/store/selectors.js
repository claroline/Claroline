import {selectors as formSelectors} from '#/main/app/content/form'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

const STORE_NAME = editorSelectors.STORE_NAME

const clacoForm = (state) => editorSelectors.resource(state)
const categories = (state) => formSelectors.value(formSelectors.form(state, editorSelectors.STORE_NAME), 'categories')
const keywords = (state) => formSelectors.value(formSelectors.form(state, editorSelectors.STORE_NAME), 'keywords')

export const selectors = {
  STORE_NAME,

  clacoForm,
  categories,
  keywords
}
