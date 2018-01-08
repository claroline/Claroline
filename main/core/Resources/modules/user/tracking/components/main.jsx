import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {generateUrl} from '#/main/core/api/router'
import {t} from '#/main/core/translation'

import {UserPageContainer} from '#/main/core/user/containers/page.jsx'
import {UserDetails} from '#/main/core/user/components/details.jsx'
import {Timeline} from '#/main/core/user/tracking/components/timeline.jsx'

const TrackingComponent = props =>
  <UserPageContainer
    customActions={[
      {
        icon: 'fa fa-fw fa-id-card-o',
        label: t('show_profile'),
        action: generateUrl('claro_user_profile', {publicUrl: props.user.meta.publicUrl})
      }, {
        icon: 'fa fa-fw fa-file-pdf-o',
        label: t('export_tracking_pdf'),
        action: () => true
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
          events={[
            {
              date: '2017-12-04T15:00:00',
              type: 'evaluation',
              status: 'success',
              progression: [18, 20],
              workspaces: [

              ],
              data: {
                resourceNode: {
                  id: '',
                  icon: '',
                  poster: '',
                  name: 'This is the related resource'
                },
                duration: 10000,
                attempts: 10
              }
            }, {
              date: '2017-12-04T12:30:00',
              type: 'content',
              progression: [50, 100],
              data: {
                resourceNode: {
                  id: '',
                  icon: '',
                  poster: '',
                  name: 'This is the related resource'
                },
                duration: 10000,
                views: 15
              }
            }, {
              date: '2017-12-04T09:10:00',
              type: 'content',
              data: {
                resourceNode: {
                  id: '',
                  icon: '',
                  poster: '',
                  name: 'This is the related resource'
                },
                duration: 10000,
                views: 20
              }
            }, {
              date: '2017-12-04T07:00:00',
              type: 'evaluation',
              status: 'partial',
              progression: [12, 20],
              workspaces: [

              ],
              data: {
                resourceNode: {
                  id: '',
                  icon: '',
                  poster: '',
                  name: 'This is the related resource'
                },
                duration: 10000,
                attempts: 10
              }
            }, {
              date: '2017-12-03T21:00:00',
              type: 'evaluation',
              status: 'failure',
              progression: [4, 20],
              workspaces: [

              ],
              data: {
                resourceNode: {
                  id: '',
                  icon: '',
                  poster: '',
                  name: 'This is the related resource'
                },
                duration: 10000,
                attempts: 10
              }
            }, {
              date: '2017-12-03T19:00:00',
              type: 'badge',
              status: 'success',
              data: {
                badge: {

                }
              }
            }
          ]}
        />
      </div>
    </div>
  </UserPageContainer>

TrackingComponent.propTypes = {
  user: T.shape({
    meta: T.shape({
      publicUrl: T.string.isRequired
    }).isRequired
  }).isRequired
}

const Tracking = connect(
  state => ({
    user: state.user
  }),
  null
)(TrackingComponent)

export {
  Tracking
}
