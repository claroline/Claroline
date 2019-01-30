import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'

const ConfirmModal = props =>
  <Modal
    {...omit(props, 'dangerous', 'question', 'confirmButtonText', 'handleConfirm', 'additional')}
  >
    <HtmlText className="modal-body">{props.question}</HtmlText>

    {props.additional}

    <CallbackButton
      className="modal-btn btn"
      callback={() => {
        props.handleConfirm()
        props.fadeModal()
      }}
      dangerous={props.dangerous}
      primary={!props.dangerous}
    >
      {props.confirmButtonText || trans('confirm')}
    </CallbackButton>
  </Modal>

ConfirmModal.propTypes = {
  dangerous: T.bool,
  question: T.string.isRequired, // It can be plain text or HTML
  additional: T.any,
  confirmButtonText: T.string,
  handleConfirm: T.func.isRequired,
  fadeModal: T.func.isRequired
}

ConfirmModal.defaultProps = {
  dangerous: false
}

export {
  ConfirmModal
}
