import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/tools/locations/room/modals/booking/store'
import {Room as RoomTypes, RoomBooking as RoomBookingTypes} from '#/main/core/tools/locations/prop-types'

const RoomBookingModal = props =>
  <Modal
    {...omit(props, 'room', 'booking', 'saveEnabled', 'loadBooking', 'saveBooking', 'onSave')}
    icon={props.booking && props.booking.id ? 'fa fa-fw fa-cog' : 'fa fa-fw fa-plus'}
    title={trans('booking', {}, 'location')}
    subtitle={props.room.name}
    onEntering={() => props.loadBooking(props.booking)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'dates',
              type: 'date-range',
              label: trans('period'),
              options: {time: true},
              required: true
            }, {
              name: 'description',
              type: 'string',
              label: trans('description'),
              options: {long: true}
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
        callback={() => props.saveBooking(props.room.id, props.booking ? props.booking.id : null, (data) => {
          props.onSave(data)
          props.fadeModal()
        })}
      />
    </FormData>
  </Modal>

RoomBookingModal.propTypes = {
  room: T.shape(
    RoomTypes.propTypes
  ).isRequired,
  booking: T.shape(
    RoomBookingTypes.propTypes
  ),
  saveEnabled: T.bool.isRequired,
  loadBooking: T.func.isRequired,
  saveBooking: T.func.isRequired,
  onSave: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  RoomBookingModal
}
