import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/agenda/tools/agenda/modals/event/store/selectors'

const EventModal = props =>
  <Modal
    {...omit(props, 'event', 'saveEnabled', 'update', 'save')}
    icon="fa fa-fw fa-info"
    title={trans('event', {}, 'agenda')}
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
              name: '_type',
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
              },
              calculated: (event) => get(event, 'meta.task') ? 'task' : 'event',
              onChange: (value) => props.update('meta.task', 'task' === value)
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
              label: trans('guests', {}, 'agenda')
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
              name: 'color',
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
        props.save(props.event)
        props.fadeModal()
      }}
    />
  </Modal>

EventModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  event: T.shape({

  }),
  update: T.func.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  EventModal
}
