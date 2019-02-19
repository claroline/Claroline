import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

const ImportModal = props => {
  return (
    <Modal
      icon="fa fa-fw fa-upload"
      {...omit(props)}
    >
      <Button
        type={CALLBACK_BUTTON}
        label={trans('import', {}, 'actions')}
        className="modal-btn btn"
        primary={true}
        disabled={false}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

ImportModal.propTypes = {

  fadeModal: T.func.isRequired
}

ImportModal.defaultProps = {

}

export {
  ImportModal
}
