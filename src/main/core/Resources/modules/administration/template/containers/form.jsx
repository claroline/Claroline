import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions, selectors} from '#/main/core/administration/template/store'
import {TemplateForm as TemplateFormComponent} from '#/main/core/administration/template/components/form'
import {actions as formActions} from '#/main/app/content/form/store'

const TemplateForm = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME + '.template')),
    template: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.template')),
    defaultLocale: selectors.defaultLocale(state),
    locales: selectors.locales(state)
  }),
  (dispatch) => ({
    saveForm(templateTypeId, templateId, isNew = false) {
      dispatch(formActions.saveForm(`${selectors.STORE_NAME}.template`, isNew ?
        ['apiv2_template_create'] :
        ['apiv2_template_update', {id: templateId}])
      ).then(() => {
        dispatch(actions.open(templateTypeId))
      })
    }
  })
)(TemplateFormComponent)

export {
  TemplateForm
}
