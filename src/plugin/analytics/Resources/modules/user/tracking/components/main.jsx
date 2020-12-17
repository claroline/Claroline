import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {User} from '#/main/core/user/prop-types'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'
import {UserDetails} from '#/main/core/user/components/details'
import {ProfileLayout} from '#/main/core/user/profile/components/layout'

import {Timeline} from '#/plugin/analytics/user/tracking/components/timeline'
import {Search} from '#/plugin/analytics/user/tracking/components/search'
import {Summary} from '#/plugin/analytics/user/tracking/components/summary'

class Tracking extends Component {
  constructor(props) {
    super(props)

    this.state = {
      filters: {
        startDate: null,
        endDate: null
      }
    }
    this.updateProp = this.updateProp.bind(this)
  }

  componentDidMount() {
    this.props.loadTracking(this.props.user.id, this.state.filters.startDate, this.state.filters.endDate)
  }

  updateProp(propName, propValue) {
    const filters = merge({}, this.state.filters, {[propName]: propValue.replace(/T.*$/i, '')})
    this.setState(() => ({filters: filters}))
  }

  render() {
    return (
      <ProfileLayout
        affix={
          <UserDetails
            user={this.props.user}
          />
        }
        content={
          <Fragment>
            <h2>{trans('activities_tracking')}</h2>

            <Search
              startDate={this.state.filters.startDate}
              endDate={this.state.filters.endDate}
              onChange={this.updateProp}
              onSearch={(start, end) => this.props.loadTracking(this.props.user.id, start, end)}
            />

            <Summary
              evaluations={this.props.evaluations}
            />

            <Timeline
              events={this.props.evaluations.map(e => ({
                date: e.date,
                type: 'evaluation',
                data: e
              }))}
            />
          </Fragment>
        }
      />
    )
  }
}

Tracking.propTypes = {
  user: T.shape(
    User.propTypes
  ).isRequired,
  evaluations: T.arrayOf(T.shape(
    UserEvaluationTypes.propTypes
  )),
  loadTracking: T.func.isRequired
}

export {
  Tracking
}
