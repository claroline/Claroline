import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {viewComponents} from '../views'

let AdminTaskToolLayout = props =>
  <div>
    {React.createElement(viewComponents[props.viewMode])}
  </div>

AdminTaskToolLayout.propTypes = {
  viewMode: T.string.isRequired
}

function mapStateToProps(state) {
  return {
    viewMode: state.viewMode
  }
}

AdminTaskToolLayout = connect(mapStateToProps)(AdminTaskToolLayout)

export {AdminTaskToolLayout}