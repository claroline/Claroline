import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'

import {Modal} from '#/main/app/overlay/modal/components/modal'
import {Event} from '#/plugin/agenda/tools/agenda/components/event'

const EventModal = props =>
  <Modal
    {...omit(props, 'event', 'onForm', 'onDelete')}
    icon="fa fa-fw fa-info"
    title={trans('event', {}, 'agenda')}
  >
    <Event
      {...props.event}
      onForm={() => {
        props.fadeModal()
        props.onForm()
      }}
      onDelete={() => {
        props.fadeModal()
        props.onDelete()
      }}
    />
  </Modal>

EventModal.propTypes = {
  event: T.object.isRequired,
  onForm: T.func.isRequired,
  onDelete: T.func.isRequired
}

export {
  EventModal
}
