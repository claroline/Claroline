import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'

import {EventList} from '#/plugin/cursus/event/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/event/store'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_TRAINING_SESSIONS} from '#/plugin/cursus/modals/sessions'
import {MODAL_TRAINING_EVENT_FORM} from '#/plugin/cursus/event/modals/form'

const EventsList = (props) =>
  <ToolPage
    title={trans('session_events', {}, 'cursus')}
  >
    <PageListSection
      title={trans('session_events', {}, 'cursus')}
      addAction={{
        type: MODAL_BUTTON,
        label: trans('plan_training_event', {}, 'actions'),
        modal: [MODAL_TRAINING_SESSIONS, {
          url: ['apiv2_cursus_session_context_list', {context: props.contextType, contextId: props.contextId}],
          multiple: false,
          selectAction: (selected) => ({
            type: MODAL_BUTTON,
            label: trans('plan_training_event', {}, 'actions'),
            modal: [MODAL_TRAINING_EVENT_FORM, {
              session: selected[0],
              isNew: true,
              onSave: props.invalidateList
            }]
          })
        }],
        displayed: props.canEdit
      }}
    >
      <EventList
        className="mb-5"
        flush={true}
        path={props.path}
        name={selectors.STORE_NAME+'.list'}
        url={['apiv2_training_event_list', {workspace: props.contextId}]}
        customDefinition={[
          {
            name: 'session',
            label: trans('session', {}, 'cursus'),
            type: 'training_session',
            displayed: true
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

EventsList.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  invalidateList: T.func.isRequired,
  canEdit: T.bool.isRequired
}

export {
  EventsList
}
