import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {useFetch, actions as fetchActions} from '#/main/app/api/fetch'
import {selectors as securitySelectors} from '#/main/app/security'
import {
  PageContent,
  PageHeading,
  PageHeadingSkeleton,
  PageSection,
  PageTabbedSection,
  PageToolbar,
  PageToolbarSkeleton
} from '#/main/app/page'
import {ToolPage} from '#/main/core/tool'

import {getActions} from '#/plugin/cursus/session/utils'
import {DetailsData} from '#/main/app/content/details'
import {Content} from '#/main/app/components/content'
import {selectors} from '#/plugin/cursus/session/store'
import {SessionUsers} from '#/plugin/cursus/session/components/users'
import {AvailableSeats} from '#/plugin/cursus/components/available-seats'
import {EventList} from '#/plugin/cursus/event/components/list'

const SessionShow = (props) => {
  const dispatch = useDispatch()
  const history = useHistory()

  const currentUser = useSelector(securitySelectors.currentUser)
  const [session] = useFetch(selectors.STORE_NAME, ['apiv2_cursus_session_get', {id: props.id}])

  return (
    <ToolPage
      title={trans('session_name', {name: get(session, 'name', trans('loading'))}, 'cursus')}
      description={get(session, 'plainDescription')}
    >
      {!session &&
        <PageContent className="placeholder-glow">
          <PageToolbarSkeleton toolbar="edit more" />
          <PageHeadingSkeleton
            description={true}
          />
        </PageContent>
      }

      {session &&
        <PageContent poster={session.poster} className="d-flex flex-column">
          <PageToolbar
            toolbar="edit more"
            actions={getActions([session], {
              add: () => dispatch(fetchActions.invalidate('trainingSession')),
              update: () => dispatch(fetchActions.invalidate('trainingSession')),
              delete: () => {
                history.push(props.path+'/sessions')
                dispatch(fetchActions.invalidate('trainingSession'))
              }
            }, props.path, currentUser)}
          />

          <PageHeading
            title={session.name}
            description={session.plainDescription}
          />

          <PageSection className="mb-5">
            <AvailableSeats session={session} className="fs-base lh-base py-2 px-3 mb-4" />
            <DetailsData
              data={session}
              definition={[
                {
                  title: trans('general'),
                  primary: true,
                  fields: [
                    {
                      name: 'dates',
                      type: 'date-range',
                      label: trans('date')
                    }, {
                      name: 'location',
                      type: 'location',
                      label: trans('location'),
                      placeholder: trans('online_session', {}, 'cursus')
                    }, {
                      name: 'course',
                      type: 'training_course',
                      label: trans('course', {}, 'cursus')
                    }, {
                      name: 'code',
                      type: 'string',
                      label: trans('code')
                    }
                  ]
                }
              ]}
            />
          </PageSection>

          <PageTabbedSection
            className="mb-5"
            tabs={[
              {
                name: 'about',
                title: trans('about'),
                displayed: !!get(session, 'description'),
                render: () => (
                  <div className="mt-4" role="presentation">
                    <Content>
                      {get(session, 'description')}
                    </Content>
                  </div>
                )
              }, {
                name: 'events',
                title: trans('session_events', {}, 'cursus'),
                render: () => (
                  <>
                    <EventList
                      className="mt-4"
                      path={props.path}
                      name={selectors.STORE_NAME+'.events'}
                      url={['apiv2_cursus_session_list_events', {id: session.id}]}
                    />
                  </>
                )
              }, {
                name: 'participants',
                title: trans('participants'),
                render: () => (
                  <SessionUsers
                    className="mt-4"
                    path={props.path}
                    name={selectors.STORE_NAME+'.users'}
                    url={['apiv2_training_session_user_course_list', {id: session.course.id, sessionId: session.id}]}
                    registrationForm={get(session, 'registration.form')}
                    confirmation={get(session, 'registration.userValidation', false)}
                    validation={get(session, 'registration.validation', false)}
                  />
                )
              }
            ]}
          />
        </PageContent>
      }
    </ToolPage>
  )
}

SessionShow.propTypes = {
  path: T.string.isRequired,
  id: T.string.isRequired
}

export {
  SessionShow
}
