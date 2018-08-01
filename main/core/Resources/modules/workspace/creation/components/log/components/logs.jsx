import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

const Logs = props => {
  return (<div>
    <div className="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
      <div className="panel panel-default">
        <div className="panel-heading" role="tab" id="headingOne">
          <h4 className="panel-title">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              {trans('log')}
            </a>
          </h4>
        </div>
        <div id="collapseOne" className="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
          <div className="panel-body">
            <pre>
              {props.data.log}
            </pre>
          </div>
        </div>
      </div>
    </div>
  </div>)
}

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
