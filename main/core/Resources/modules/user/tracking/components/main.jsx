import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {User} from '#/main/core/user/prop-types'
import {ResourceUserEvaluation} from '#/main/core/user/tracking/prop-types'

import {UserPageContainer} from '#/main/core/user/containers/page.jsx'
import {UserDetails} from '#/main/core/user/components/details.jsx'
import {Timeline} from '#/main/core/user/tracking/components/timeline.jsx'

const TrackingComponent = props =>
  <UserPageContainer
    customActions={[
      {
        type: 'url',
        icon: 'fa fa-fw fa-id-card-o',
        label: trans('show_profile', {}, 'platform'),
        target: ['claro_user_profile', {publicUrl: props.user.meta.publicUrl}]
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
          user={props.user}
        />
      </div>

      <div className="col-md-9">
        <h2>Suivi des activités</h2>

        {/* TODO add search */}

        <Timeline
          events={props.evaluations.map(e => {return {
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
  </UserPageContainer>

TrackingComponent.propTypes = {
  user: T.shape(User.propTypes).isRequired,
  evaluations: T.arrayOf(T.shape(ResourceUserEvaluation.propTypes))
}

const Tracking = connect(
  state => ({
    user: state.user,
    evaluations: state.evaluations
  }),
  null
)(TrackingComponent)

export {
  Tracking
}
