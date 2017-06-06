import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {viewComponents} from '../views'
import {selectors} from '../selectors'

let SessionEventsToolLayout = props =>
  <div>
    {React.createElement(viewComponents[props.viewMode])}
  </div>

SessionEventsToolLayout.propTypes = {
  viewMode: T.string.isRequired
}

function mapStateToProps(state) {
  return {
    viewMode: selectors.viewMode(state)
  }
}

SessionEventsToolLayout = connect(mapStateToProps)(SessionEventsToolLayout)

export {SessionEventsToolLayout}