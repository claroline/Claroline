import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CatalogForm as CatalogFormComponent} from '#/plugin/cursus/home/catalog/components/form'

const CatalogForm = connect(
  (state) => ({
    course: formSelectors.originalData(formSelectors.form(state, selectors.FORM_NAME))
  })
)(CatalogFormComponent)

export {
  CatalogForm
}
