import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {merge} from 'lodash'

import {trans} from '#/main/app/intl/translation'
import {User} from '#/main/core/user/prop-types'
import {actions} from '#/main/core/user/tracking/store'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'
import {UserPage} from '#/main/core/user/components/page'
import {UserDetails} from '#/main/core/user/components/details'
import {Timeline} from '#/main/core/user/tracking/components/timeline'
import {Search} from '#/main/core/user/tracking/components/search'
import {Summary} from '#/main/core/user/tracking/components/summary'
import {route} from '#/main/core/user/routing'

class TrackingComponent extends Component {
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

  updateProp(propName, propValue) {
    const filters = merge({}, this.state.filters, {[propName]: propValue.replace(/T.*$/i, '')})
    this.setState(() => ({filters: filters}))
  }

  render() {
    return(
      <UserPage
        customActions={[
          {
            type: 'url',
            icon: 'fa fa-fw fa-address-card',
            label: trans('show_profile', {}, 'platform'),
            target: route(this.props.user)
          }, {
            type: 'callback',
            icon: 'fa fa-fw fa-file-pdf-o',
            label: trans('export_tracking_pdf', {}, 'platform'),
            callback: () => true
          }
        ]}
      >
        <div className="row">
          <div className="col-md-3">
            <UserDetails
              user={this.props.user}
            />
          </div>

          <div className="col-md-9">
            <h2>{trans('activities_tracking')}</h2>

            <Search
              startDate={this.state.filters.startDate}
              endDate={this.state.filters.endDate}
              onChange={this.updateProp}
              onSearch={this.props.loadTrackings}
            />

            <Summary
              evaluations={this.props.evaluations}
            />

            <Timeline
              events={this.props.evaluations.map(e => {return {
                date: e.date,
                type: 'evaluation',
                status: e.status,
                progression: e.score !== null && e.scoreMax !== null ? [e.score, e.scoreMax] : null,
                data: {
                  resourceNode: e.resourceNode,
                  nbAttempts: e.nbAttempts,
                  nbOpenings: e.nbOpenings,
                  duration: e.duration
                }
              }})}
            />
          </div>
        </div>
      </UserPage>
    )
  }
}

TrackingComponent.propTypes = {
  user: T.shape(User.propTypes).isRequired,
  evaluations: T.arrayOf(T.shape(UserEvaluationTypes.propTypes)),
  loadTrackings: T.func.isRequired
}

const Tracking = connect(
  state => ({
    user: state.user,
    evaluations: state.evaluations
  }),
  dispatch => ({
    loadTrackings(startDate, endDate) {
      dispatch(actions.loadTrackings(startDate, endDate))
    }
  })
)(TrackingComponent)

export {
  Tracking
}
