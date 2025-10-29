import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {hasPermission, selectors as securitySelectors} from '#/main/app/security'
import {actions as fetchActions, useFetch} from '#/main/app/api/fetch'
import {
  PageContent,
  PageHeading,
  PageHeadingSkeleton,
  PageSection,
  PageToolbar,
  PageToolbarSkeleton
} from '#/main/app/page'
import {route, ToolPage} from '#/main/core/tool'
import {DetailsData} from '#/main/app/content/details'
import {Content} from '#/main/app/components/content'

import {getActions} from '#/plugin/agenda/events/event/utils'
import {selectors} from '#/plugin/agenda/events/event/store'
import {CalendarIcon} from '#/main/app/calendar/components/icon'
import {EventParticipants} from '#/plugin/agenda/events/event/containers/participants'
import {selectors as contextSelectors} from '#/main/app/context'

const TaskShow = (props) => {
  const dispatch = useDispatch()
  const history = useHistory()

  const currentUser = useSelector(securitySelectors.currentUser)
  const contextPath = useSelector(contextSelectors.path)
  const [event] = useFetch(selectors.STORE_NAME, ['apiv2_task_get', {id: props.id}])

  return (
    <ToolPage
      className="event-page"
      title={trans('task_name', {name: get(event, 'name', trans('loading'))}, 'agenda')}
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
                history.push(route('agenda', contextPath)+'/events')
                dispatch(fetchActions.invalidate(selectors.STORE_NAME))
              }
            }, contextPath, currentUser)}
          />

          <PageHeading
            icon={
              <CalendarIcon square={true} size="lg" date={event.start} />
            }
            title={event.name}
          />

          {get(event, 'description') &&
            <PageSection className="mb-5">
              <Content>
                {get(event, 'description')}
              </Content>
            </PageSection>
          }

          <PageSection className="mb-5">
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
                      placeholder: trans('online')
                    }
                  ]
                }
              ]}
            />
          </PageSection>

          {event &&
            <EventParticipants
              eventId={event.id}
              canEdit={!!event && hasPermission('edit', event)}
            />
          }
        </PageContent>
      }
    </ToolPage>
  )
}

TaskShow.propTypes = {
  id: T.string.isRequired
}

export {
  TaskShow
}
