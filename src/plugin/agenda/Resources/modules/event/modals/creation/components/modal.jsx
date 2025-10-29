import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import moment from 'moment'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans, now} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentMenu} from '#/main/app/content/components/menu'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvents} from '#/plugin/agenda/event/registry'
import {MODAL_EVENT_FORM} from '#/plugin/agenda/event/modals/form'

const EventCreationModal = (props) => {
  const [eventTypes, setEventTypes] = useState([])

  const contextType = useSelector(toolSelectors.contextType)
  const contextData = useSelector(toolSelectors.contextData)
  const contextTools = useSelector(toolSelectors.contextTools)

  useEffect(() => {
    getEvents().then((events) => setEventTypes(events.filter(event => {
      if (event.canCreate) {
        return event.canCreate(contextType, contextData, contextTools)
      }

      return true
    })))
  }, [contextType, contextData, contextTools])

  // initialize the form with default values
  const start = props.event && props.event.start ? props.event.start : now(false)
  const end = moment(start, 'YYYY-MM-DDThh:mm:ss')
  // default event duration to 1 hour
  end.add(1, 'h')

  return (
    <Modal
      {...omit(props, 'event', 'onCreate')}
      title={trans('new_event', {}, 'agenda')}
      subtitle={trans('new_event_select', {}, 'agenda')}
      centered={true}
    >
      <div className="modal-body" role="presentation">
        <ContentMenu
          className="mb-3"
          items={eventTypes.map(type => ({
            id: type.name,
            icon: type.icon,
            label: trans(type.name, {}, 'event'),
            description: trans(`${type.name}_desc`, {}, 'event'),
            action: {
              type: MODAL_BUTTON,
              modal: [MODAL_EVENT_FORM, {
                title: trans('new_event', {}, 'agenda'),
                subtitle: trans('new_event_configure', {}, 'agenda'),
                isNew: true,
                event: merge({}, EventTypes.defaultProps, props.event, {
                  start: start,
                  end: end.format('YYYY-MM-DDThh:mm:ss'),
                  meta: {
                    type: type.name
                  },
                  workspace: 'workspace' === contextType ? contextData : null
                }),
                onSave: (created) => {
                  if (props.onCreate) {
                    props.onCreate(created)
                  }
                  props.fadeModal()
                }
              }]
            }
          }))}
        />
      </div>
    </Modal>
  )
}

EventCreationModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ),
  onCreate: T.func,
  fadeModal: T.func.isRequired
}

export {
  EventCreationModal
}
