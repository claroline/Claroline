import {connect} from 'react-redux'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CatalogDetails as CatalogDetailsComponent} from '#/plugin/cursus/home/catalog/components/details'

const CatalogDetails = connect(
  (state) => ({
    course: selectors.course(state),
    activeSession: selectors.activeSession(state)
  })
)(CatalogDetailsComponent)

export {
  CatalogDetails
}
