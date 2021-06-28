import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

import {CatalogDetails as CatalogDetailsComponent} from '#/plugin/cursus/tools/trainings/catalog/components/details'

const CatalogDetails = connect(
  (state) => ({
    currentContext: toolSelectors.context(state),
    course: selectors.course(state),
    activeSession: selectors.activeSession(state)
  })
)(CatalogDetailsComponent)

export {
  CatalogDetails
}
