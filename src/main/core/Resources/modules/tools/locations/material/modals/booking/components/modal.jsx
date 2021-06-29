import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/tools/locations/material/modals/booking/store'
import {Material as MaterialTypes, MaterialBooking as MaterialBookingTypes} from '#/main/core/tools/locations/prop-types'

const MaterialBookingModal = props =>
  <Modal
    {...omit(props, 'material', 'booking', 'saveEnabled', 'loadBooking', 'saveBooking', 'onSave')}
    icon={props.booking && props.booking.id ? 'fa fa-fw fa-cog' : 'fa fa-fw fa-plus'}
    title={trans('booking', {}, 'location')}
    subtitle={props.material.name}
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
        callback={() => props.saveBooking(props.material.id, props.booking ? props.booking.id : null, (data) => {
          props.onSave(data)
          props.fadeModal()
        })}
      />
    </FormData>
  </Modal>

MaterialBookingModal.propTypes = {
  material: T.shape(
    MaterialTypes.propTypes
  ).isRequired,
  booking: T.shape(
    MaterialBookingTypes.propTypes
  ),
  saveEnabled: T.bool.isRequired,
  loadBooking: T.func.isRequired,
  saveBooking: T.func.isRequired,
  onSave: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  MaterialBookingModal
}
