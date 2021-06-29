import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/tools/locations/room/modals/parameters/store'
import {Room as RoomTypes} from '#/main/core/tools/locations/prop-types'

const RoomParametersModal = props =>
  <Modal
    {...omit(props, 'room', 'saveEnabled', 'loadRoom', 'saveRoom', 'onSave')}
    icon={props.room && props.room.id ? 'fa fa-fw fa-cog' : 'fa fa-fw fa-plus'}
    title={trans('room', {}, 'location')}
    subtitle={props.room && props.room.id ? props.room.name : trans('new_room', {}, 'location')}
    onEntering={() => props.loadRoom(props.room)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code'),
              required: true
            }, {
              name: 'capacity',
              type: 'number',
              label: trans('capacity'),
              required: true,
              options: {
                min: 0
              }
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
              name: 'location',
              type: 'location',
              label: trans('location')
            }, {
              name: 'organizations',
              type: 'organizations',
              label: trans('organizations'),
              displayed: false
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'poster',
              type: 'image',
              label: trans('poster')
            }, {
              name: 'thumbnail',
              type: 'image',
              label: trans('thumbnail')
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn"
        type={CALLBACK_BUTTON}
        htmlType="submit"
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => props.saveRoom(props.room ? props.room.id : null, (data) => {
          props.onSave(data)
          props.fadeModal()
        })}
      />
    </FormData>
  </Modal>

RoomParametersModal.propTypes = {
  room: T.shape(
    RoomTypes.propTypes
  ),
  saveEnabled: T.bool.isRequired,
  loadRoom: T.func.isRequired,
  saveRoom: T.func.isRequired,
  onSave: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  RoomParametersModal
}
