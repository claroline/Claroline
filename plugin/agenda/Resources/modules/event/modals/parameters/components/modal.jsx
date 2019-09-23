import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'
import {selectors} from '#/plugin/agenda/event/modals/parameters/store/selectors'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'event', 'saveEnabled', 'loadEvent', 'update', 'save')}
    icon="fa fa-fw fa-cog"
    title={props.event.id ? props.event.title : trans('new_event', {}, 'agenda')}
    subtitle={trans('parameters')}
    onEntering={() => props.loadEvent(props.event)}
  >
    <FormData
      name={selectors.STORE_NAME}
      meta={true}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'meta.type',
              label: trans('type'),
              type: 'choice',
              hideLabel: true,
              options: {
                multiple: false,
                condensed: false,
                inline: true,
                choices: {
                  event: trans('event'),
                  task: trans('task')
                }
              }
            }, {
              name: 'title',
              type: 'string',
              label: trans('title'),
              required: true
            }, {
              name: 'dates',
              type: 'date-range',
              label: trans('date'),
              required: true,
              calculated: (event) => [event.start || null, event.end || null],
              onChange: (datesRange) => {
                props.update('start', datesRange[0])
                props.update('end', datesRange[1])
              },
              options: {
                time: true
              }
            }, {
              name: 'allDay',
              type: 'boolean',
              label: trans('all_day', {}, 'agenda')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-info',
          title: trans('information'),
          fields: [
            {
              name: 'description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'guests',
              type: 'users',
              label: trans('guests')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'thumbnail',
              type: 'image',
              label: trans('thumbnail')
            }, {
              name: 'display.color',
              type: 'color',
              label: trans('color')
            }
          ]
        }
      ]}
    />

    <Button
      className="modal-btn btn btn-primary"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.event, props.onSave)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ),
  onSave: T.func,
  // from store
  saveEnabled: T.bool.isRequired,
  loadEvent: T.func.isRequired,
  update: T.func.isRequired,
  save: T.func.isRequired,
  // from modal
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
