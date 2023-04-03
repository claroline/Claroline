import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CatalogEdit as CatalogEditComponent} from '#/plugin/cursus/tools/trainings/catalog/components/edit'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CatalogEdit = connect(
  (state) =>({
    path: toolSelectors.path(state),
    course: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  })
)(CatalogEditComponent)

export {
  CatalogEdit
}
