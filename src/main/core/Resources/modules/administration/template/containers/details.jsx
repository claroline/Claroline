import cloneDeep from 'lodash/cloneDeep'
import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TemplateDetails as TemplateDetailsComponent} from '#/main/core/administration/template/components/details'
import {actions, selectors} from '#/main/core/administration/template/store'
import {makeId} from '#/main/core/scaffolding/id'

const TemplateDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    templateType: selectors.templateType(state),
    templates: selectors.templates(state),
    defaultLocale: selectors.defaultLocale(state)
  }),
  (dispatch) => ({
    openForm(templateType, defaultLocale, id = null) {
      const defaultData = {
        lang: defaultLocale,
        type: cloneDeep(templateType)
      }
      dispatch(actions.openForm(selectors.STORE_NAME + '.template', defaultData, id))
    },
    resetForm(templateType, defaultLocale) {
      const defaultData = {
        id: makeId(),
        lang: defaultLocale,
        type: cloneDeep(templateType)
      }
      dispatch(actions.resetForm(selectors.STORE_NAME + '.template', defaultData))
    },
    deleteTemplate(templateTypeId, templateId) {
      return dispatch(actions.deleteTemplate(templateTypeId, templateId))
    }
  })
)(TemplateDetailsComponent)

export {
  TemplateDetails
}
