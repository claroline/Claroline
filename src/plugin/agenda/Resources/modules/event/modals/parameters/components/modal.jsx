import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventParameters} from '#/plugin/agenda/event/components/parameters'
import {selectors} from '#/plugin/agenda/event/modals/parameters/store/selectors'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'event', 'formData', 'saveEnabled', 'loadEvent', 'update', 'save', 'onSave')}
    icon={props.event.id ? 'fa fa-fw fa-cog' : 'fa fa-fw fa-plus'}
    title={props.event.id ? props.event.name : trans('new_event', {}, 'agenda')}
    subtitle={trans('parameters')}
    onEntering={() => props.loadEvent(props.event)}
  >
    {!isEmpty(props.formData) &&
      <EventParameters
        name={selectors.STORE_NAME}
        event={props.formData}
        update={props.update}
        onSave={(response) => {
          props.onSave(response)
          props.fadeModal()
        }}
      />
    }
  </Modal>

ParametersModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ),
  onSave: T.func,
  // from store
  formData: T.object,
  loadEvent: T.func.isRequired,
  update: T.func.isRequired,
  // from modal
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
