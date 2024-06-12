import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationForm as LocationFormComponent} from '#/main/core/tools/locations//components/form'

const LocationForm = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(LocationFormComponent)

export {
  LocationForm
}
