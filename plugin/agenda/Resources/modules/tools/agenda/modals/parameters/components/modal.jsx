import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/agenda/tools/agenda/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'save')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={trans('agenda', {}, 'tools')}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [

          ]
        }
      ]}
    />

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save()
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
