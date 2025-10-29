import React from 'react'
import {useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {EventList} from '#/plugin/agenda/event/components/list'
import {route} from '#/plugin/agenda/event/routing'
import {selectors} from '#/plugin/agenda/tools/agenda/store'

import {MODAL_EVENT_CREATION} from '#/plugin/agenda/event/modals/creation'

const AgendaList = () => {
  const currentUser = useSelector(securitySelectors.currentUser)
  const contextType = useSelector(contextSelectors.type)
  const contextData = useSelector(contextSelectors.data)
  const contextPath = useSelector(contextSelectors.path)

  return (
    <ToolPage title={trans('all_events', {}, 'agenda')}>
      <PageListSection
        title={trans('all_events', {}, 'agenda')}
        addAction={{
          type: MODAL_BUTTON,
          label: trans('add_event', {}, 'actions'),
          modal: [MODAL_EVENT_CREATION, {
            onCreate: () => true // TODO invalidate
          }],
          displayed: !isEmpty(currentUser)
        }}
      >
        <EventList
          className="mb-5"
          flush={true}
          name={selectors.STORE_NAME+'.list'}
          url={['apiv2_planned_object_planning_list', {planningId: (contextData && contextData.id) || currentUser.id}]}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: route(row, contextPath)
          })}
          customDefinition={'desktop' === contextType ? [
            {
              name: 'workspace',
              type: 'workspace',
              label: trans('workspace'),
              displayed: true,
              filterable: false,
              sortable: false
            }
          ] : []}
        />
      </PageListSection>
    </ToolPage>
  )
}

export {
  AgendaList
}
