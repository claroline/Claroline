import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {constants} from '#/plugin/agenda/event/constants'
import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'
import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'

const AboutModal = props =>
  <Modal
    {...omit(props, 'event')}
    icon="fa fa-fw fa-info"
    title={props.event.title}
    subtitle={trans('about')}
    poster={props.event.thumbnail ? props.event.thumbnail.url : undefined}
  >
    <DetailsData
      data={props.event}
      meta={true}
      sections={[{
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'dates',
            type: 'date-range',
            label: trans('date'),
            calculated: (event) => [event.start || null, event.end || null],
            options: {
              time: true
            }
          }, {
            name: 'description',
            type: 'html',
            label: trans('description')
          }, {
            name: 'guests',
            type: 'users',
            label: trans('guests')
          }
        ]
      }]}
    />

    <Toolbar
      id={`event-${props.event.id}-actions`}
      buttonName="modal-btn btn"
      actions={[
        {
          name: 'mark-done',
          type: CALLBACK_BUTTON,
          label: trans('mark-as-done', {}, 'actions'),
          callback: () => true,
          displayed: constants.EVENT_TYPE_TASK === props.event.meta.type && !props.event.meta.done
        }, {
          name: 'mark-todo',
          type: CALLBACK_BUTTON,
          label: trans('mark-as-todo', {}, 'actions'),
          callback: () => true,
          displayed: constants.EVENT_TYPE_TASK === props.event.meta.type && props.event.meta.done
        }, {
          name: 'edit',
          type: MODAL_BUTTON,
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_EVENT_PARAMETERS, {
            event: props.event
          }],
          displayed: props.event.permissions.edit
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          label: trans('delete', {}, 'actions'),
          callback: () => props.fadeModal(),
          dangerous: true,
          displayed: props.event.permissions.edit
        }
      ]}
    />
  </Modal>

AboutModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired
}

export {
  AboutModal
}
