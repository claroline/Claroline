import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CatalogForm as CatalogFormComponent} from '#/plugin/cursus/tools/trainings/catalog/components/form'

const CatalogForm = connect(
  (state) => ({
    //path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    isNew: formSelectors.isNew(formSelectors.form(state, selectors.FORM_NAME)),
    formData: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
    course: formSelectors.originalData(formSelectors.form(state, selectors.FORM_NAME))
  })
)(CatalogFormComponent)

export {
  CatalogForm
}
