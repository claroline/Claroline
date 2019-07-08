import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

const Logs = props =>
  <div className="panel panel-default">
    <div className="panel-heading">
      <h4 className="panel-title">
        {trans('log')}
      </h4>
    </div>

    <div className="panel-body">
      <pre>
        {props.data.log}
      </pre>
    </div>
  </div>

Logs.propTypes = {
  data: T.object.isRequired
}

const ConnectedLog = connect(
  state => ({
    data: state.workspaces.creation.log
  }),
  null
)(Logs)

export {
  ConnectedLog as Logs
}
