import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {number, fileSize} from '#/main/app/intl'
import {trans} from '#/main/core/translation'
import {CountGauge} from '#/main/core/layout/gauge/components/count-gauge'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const WorkspaceMetrics = props =>
  <div className="row">
    <div className="col-md-4 col-sm-4 col-xs-4">
      <div className="metric-card">
        <CountGauge
          className="metric-card-gauge"
          value={props.workspace.meta.totalUsers}
          total={props.workspace.restrictions.maxUsers}
          displayValue={(value) => number(value, true)}
          width={props.width}
          height={props.height}
        />

        <div className={classes('metric-card-title', `h${props.level}`)}>{trans('registered_users')}</div>
      </div>
    </div>

    <div className="col-md-4 col-sm-4 col-xs-4">
      <div className="metric-card">
        <CountGauge
          className="metric-card-gauge"
          value={props.workspace.meta.totalResources}
          total={props.workspace.restrictions.maxResources}
          displayValue={(value) => number(value, true)}
          width={props.width}
          height={props.height}
        />

        <div className={classes('metric-card-title', `h${props.level}`)}>{trans('resources')}</div>
      </div>
    </div>

    <div className="col-md-4 col-sm-4 col-xs-4">
      <div className="metric-card">
        <CountGauge
          className="metric-card-gauge"
          value={props.workspace.meta.usedStorage}
          total={props.workspace.restrictions.maxStorage}
          displayValue={(value) => fileSize(value, true)}
          unit={trans('bytes_short')}
          width={props.width}
          height={props.height}
        />

        <div className={classes('metric-card-title', `h${props.level}`)}>{trans('storage_used')}</div>
      </div>
    </div>
  </div>

WorkspaceMetrics.propTypes = {
  level: T.number,
  width: T.number,
  height: T.number,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired
}

WorkspaceMetrics.defaultProps = {
  level: 4
}

export {
  WorkspaceMetrics
}
