import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

const EditorModal = props => {
  return (
    <Modal
      icon="fa fa-fw fa-pencil"
      title={trans('edition')}
      {...omit(props)}
    >
      <FormData />

      <Button
        type={CALLBACK_BUTTON}
        label={trans('save', {}, 'actions')}
        className="modal-btn btn"
        primary={true}
        disabled={false}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

EditorModal.propTypes = {

  fadeModal: T.func.isRequired
}

EditorModal.defaultProps = {

}

export {
  EditorModal
}
