import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {getTemplateErrors} from '#/plugin/claco-form/resources/claco-form/template'
import {ClacoFormEditor as ClacoFormEditorComponent} from '#/plugin/claco-form/resources/claco-form/editor/components/main'

const ClacoFormEditor = connect(
  null,
  (dispatch) => ({
    validateTemplate(template, fields, errors = {}) {
      if (template) {
        const formErrors = Object.assign({}, errors, {
          template: {content: getTemplateErrors(template, fields)}
        })

        dispatch(formActions.setErrors(resourceSelectors.EDITOR_NAME, formErrors))
      }
    }
  })
)(ClacoFormEditorComponent)

export {
  ClacoFormEditor
}
