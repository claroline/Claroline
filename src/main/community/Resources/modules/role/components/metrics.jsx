import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {ContentCounter} from '#/main/app/content/components/counter'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class RoleMetrics extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: false,
      current: moment().year(),
      available: [
        moment().year(),
        moment().year() - 1,
        moment().year() - 2
      ],
      count: {
        users: 20,
        connections: 50,
        actions: 90
      }
    }
  }

  componentDidMount() {
    this.props.load(this.state.current).then((response) => this.setState({
      count: response,
      loaded: true
    }))
  }

  changeYear(year) {
    this.setState({
      loaded: false,
      current: year
    })

    // reload
    this.props.load(year).then((response) => this.setState({
      count: response,
      loaded: true
    }))
  }

  render() {
    let ellapsedDays = 365
    if (this.state.current === moment().year()) {
      // current period
      ellapsedDays = moment().diff(moment([this.state.current, '01, 01']), 'days') + 1
    }

    return (
      <div className="row component-container" style={{display: 'flex', alignItems: 'center'}}>
        <Toolbar
          buttonName="btn-block"
          style={{marginLeft: '15px'}}
          toolbar={this.state.available.map(year => 'y'+year).join(' ')}
          size="xs"
          actions={this.state.available.map(year => (
            {
              className: year === this.state.current ? 'btn' : 'btn-link',
              name: 'y'+year,
              type: CALLBACK_BUTTON,
              label: year,
              callback: () => this.changeYear(year)
            }
          ))}
        />

        <ContentCounter
          icon="fa fa-user"
          label={trans('users')}
          color={schemeCategory20c[1]}
          value={!this.state.loaded ? '?' : this.state.count.users}
          help={trans('role_analytics_users_help', {}, 'community')}
        />

        <ContentCounter
          icon="fa fa-power-off"
          label={trans('connections')}
          color={schemeCategory20c[5]}
          value={!this.state.loaded ?
            '? ' :
            this.state.count.connections + ' (' + Math.ceil(this.state.count.connections / ellapsedDays) + ' ' + trans('per_day_short') + ')'}
          help={trans('role_analytics_connections_help', {}, 'community')}
        />

        <ContentCounter
          icon="fa fa-history"
          label={trans('actions')}
          color={schemeCategory20c[9]}
          value={!this.state.loaded ?
            '? ' :
            this.state.count.actions + ' (' + Math.ceil(this.state.count.actions / ellapsedDays) + ' ' + trans('per_day_short') + ')'}
          help={trans('role_analytics_actions_help', {}, 'community')}
        />
      </div>
    )
  }
}

RoleMetrics.propTypes = {
  load: T.func.isRequired
}

export {
  RoleMetrics
}
