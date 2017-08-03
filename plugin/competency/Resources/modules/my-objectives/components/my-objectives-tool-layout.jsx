import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {viewComponents} from '../views'

let MyObjectivesToolLayout = props =>
  <div>
    {React.createElement(viewComponents[props.viewMode])}
  </div>

MyObjectivesToolLayout.propTypes = {
  viewMode: T.string.isRequired
}

function mapStateToProps(state) {
  return {
    viewMode: state.viewMode
  }
}

MyObjectivesToolLayout = connect(mapStateToProps)(MyObjectivesToolLayout)

export {MyObjectivesToolLayout}