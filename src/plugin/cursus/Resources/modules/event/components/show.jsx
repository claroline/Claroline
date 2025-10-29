import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {selectors as securitySelectors} from '#/main/app/security'
import {actions as fetchActions, useFetch} from '#/main/app/api/fetch'
import {
  PageContent,
  PageHeading,
  PageHeadingSkeleton,
  PageSection, PageTabbedSection,
  PageToolbar,
  PageToolbarSkeleton
} from '#/main/app/page'
import {route, ToolPage} from '#/main/core/tool'
import {DetailsData} from '#/main/app/content/details'
import {Content} from '#/main/app/components/content'

import {AvailableSeats} from '#/plugin/cursus/components/available-seats'
import {getActions} from '#/plugin/cursus/event/utils'
import {selectors} from '#/plugin/cursus/event/store'
import {EventUsers} from '#/plugin/cursus/event/components/users'
import {CalendarIcon} from '#/main/app/calendar/components/icon'

const EventShow = (props) => {
  const dispatch = useDispatch()
  const history = useHistory()

  const currentUser = useSelector(securitySelectors.currentUser)
  const [event] = useFetch(selectors.STORE_NAME, ['apiv2_training_event_get', {id: props.id}])

  return (
    <ToolPage
      className="event-page"
      title={trans('event_name', {name: get(event, 'name', trans('loading'))}, 'cursus')}
      description={get(event, 'description')}
    >
      {!event &&
        <PageContent className="placeholder-glow">
          <PageToolbarSkeleton toolbar="edit more" />
          <PageHeadingSkeleton icon={true} />
        </PageContent>
      }

      {event &&
        <PageContent poster={event.poster}>
          <PageToolbar
            toolbar="edit more"
            actions={getActions([event], {
              add: () => dispatch(fetchActions.invalidate(selectors.STORE_NAME)),
              update: () => dispatch(fetchActions.invalidate(selectors.STORE_NAME)),
              delete: () => {
                history.push(route('trainings', props.path)+'/events')
                dispatch(fetchActions.invalidate(selectors.STORE_NAME))
              }
            }, props.path, currentUser)}
          />

          <PageHeading
            icon={
              <CalendarIcon square={true} size="lg" date={event.start} />
            }
            title={event.name}
          />

          <PageSection className="mb-5">
            <AvailableSeats session={event} className="fs-base lh-base py-2 px-3 mb-4" />
            <DetailsData
              data={event}
              definition={[
                {
                  title: trans('general'),
                  primary: true,
                  fields: [
                    {
                      name: 'dates',
                      type: 'date-range',
                      label: trans('date'),
                      calculated: (data) => [data.start || null, data.end || null],
                      options: {time: true}
                    }, {
                      name: 'location',
                      type: 'location',
                      label: trans('location'),
                      placeholder: trans('online_session', {}, 'cursus')
                    }, {
                      name: 'session',
                      type: 'training_session',
                      label: trans('session', {}, 'cursus')
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
                displayed: !!get(event, 'description'),
                render: () => (
                  <div className="mt-4" role="presentation">
                    <Content>
                      {get(event, 'description')}
                    </Content>
                  </div>
                )
              }, {
                name: 'participants',
                title: trans('participants'),
                render: () => (
                  <EventUsers
                    name={selectors.STORE_NAME+'.users'}
                    path={props.path}
                    className="mt-4"
                    url={['apiv2_training_event_user_event_list', {id: event.id}]}
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

EventShow.propTypes = {
  path: T.string.isRequired,
  id: T.string.isRequired
}

export {
  EventShow
}
