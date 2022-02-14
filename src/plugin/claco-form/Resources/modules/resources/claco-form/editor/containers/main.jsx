import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {getTemplateErrors} from '#/plugin/claco-form/resources/claco-form/template'
import {selectors} from '#/plugin/claco-form/resources/claco-form/store'

import {EditorMain as EditorMainComponent} from '#/plugin/claco-form/resources/claco-form/editor/components/main'
import {actions} from '#/plugin/claco-form/resources/claco-form/editor/store'

const EditorMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    clacoForm: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME+'.clacoFormForm')),
    errors: formSelectors.errors(formSelectors.form(state, selectors.STORE_NAME+'.clacoFormForm')),
    roles: selectors.roles(state)
  }),
  (dispatch) => ({
    validateTemplate(template, fields, errors = {}) {
      if (template) {
        const formErrors = Object.assign({}, errors, {
          template: {content: getTemplateErrors(template, fields)}
        })

        dispatch(formActions.setErrors(selectors.STORE_NAME+'.clacoFormForm', formErrors))
      }
    },
    saveCategory(clacoFormId, category, isNew) {
      dispatch(actions.saveCategory(clacoFormId, category, isNew))
    },
    deleteCategories(categories) {
      dispatch(actions.deleteCategories(categories))
    },
    assignCategory(category) {
      dispatch(actions.assignCategory(category))
    },
    addKeyword(keyword) {
      dispatch(actions.addKeyword(keyword))
    },
    updateKeyword(keyword) {
      dispatch(actions.updateKeyword(keyword))
    },
    deleteKeywords(keywords) {
      dispatch(actions.deleteKeywords(keywords))
    }
  })
)(EditorMainComponent)

export {
  EditorMain
}
