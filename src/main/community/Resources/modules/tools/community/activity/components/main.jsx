import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {displayDuration} from '#/main/app/intl'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentCounter} from '#/main/app/content/components/counter'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ActivityChart} from '#/plugin/analytics/charts/activity/containers/chart'
import {ActivityLogs} from '#/main/community/tools/community/activity/components/logs'
import {ActivityConnections} from '#/main/community/tools/community/activity/components/connections'

class ActivityMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: false
    }
  }

  componentDidMount() {
    this.props.fetch(this.props.contextId).then(() => this.setState({loaded: true}))
  }

  render() {
    return (
      <ToolPage
        path={[{
          type: LINK_BUTTON,
          label: trans('activity'),
          target: `${this.props.path}/activity`
        }]}
        subtitle={trans('activity')}
      >
        <div className="row">
          <ContentCounter
            icon="fa fa-user"
            label={trans('users')}
            color={schemeCategory20c[1]}
            value={!this.state.loaded ? '?' : this.props.count.users}
          />

          <ContentCounter
            icon="fa fa-users"
            label={trans('groups')}
            color={schemeCategory20c[5]}
            value={!this.state.loaded ? '?' : this.props.count.groups}
          />

          <ContentCounter
            icon="fa fa-clock"
            label={trans('connections')}
            color={schemeCategory20c[9]}
            value={!this.state.loaded ? '?' : (get(this.props.count, 'connections.count') + (get(this.props.count, 'connections.avgTime') ?
              ' ('+trans('connection_avg_time', {time: displayDuration(this.props.count.connections.avgTime)}, 'analytics')+')' :
              ''
            ))}
          />
        </div>

        <div className="row">
          <div className="col-md-12">
            <ActivityChart url={['apiv2_community_activity_global', {contextId: this.props.contextId}]} />
          </div>
        </div>

        <div className="row">
          <div className="col-md-6">
            <ContentTitle title={trans('activity')} />
            <ActivityLogs contextId={this.props.contextId} actionTypes={this.props.actionTypes} />
          </div>

          <div className="col-md-6">
            <ContentTitle title={trans('connections')} />
            <ActivityConnections contextId={this.props.contextId} />
          </div>
        </div>
      </ToolPage>
    )
  }
}
  

ActivityMain.propTypes = {
  path: T.string.isRequired,
  contextId: T.string.isRequired,
  actionTypes: T.array,
  count: T.shape({
    users: T.number,
    roles: T.number,
    groups: T.number,
    connections: T.shape({
      count: T.number,
      avgTime: T.number
    })
  }).isRequired,
  canEdit: T.bool.isRequired,
  fetch: T.func.isRequired
}

export {
  ActivityMain
}
