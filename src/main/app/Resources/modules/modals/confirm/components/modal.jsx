import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Action as ActionTypes} from '#/main/app/action/prop-types'

const ConfirmModal = props =>
  <Modal
    {...omit(props, 'dangerous', 'question', 'additional', 'confirmAction', 'handleConfirm')}
  >
    <ContentHtml className="modal-body">{props.question || trans('action_confirm_message')}</ContentHtml>

    {props.additional}

    {props.confirmAction &&
      <Button
        label={trans('confirm', {}, 'actions')}

        {...omit(props.confirmAction, 'icon', 'tooltip', 'size')}

        className="modal-btn"
        size="lg"
        variant="btn"
        onClick={props.fadeModal}
        dangerous={props.dangerous}
        primary={!props.dangerous}
      />
    }

    {!props.confirmAction &&
      <CallbackButton
        className="modal-btn"
        variant="btn"
        size="lg"
        callback={() => {
          props.handleConfirm()
          props.fadeModal()
        }}
        dangerous={props.dangerous}
        primary={!props.dangerous}
      >
        {props.confirmButtonText || trans('confirm', {}, 'actions')}
      </CallbackButton>
    }
  </Modal>

ConfirmModal.propTypes = {
  dangerous: T.bool,
  question: T.string.isRequired, // It can be plain text or HTML
  additional: T.any,
  confirmAction: T.shape(
    ActionTypes.propTypes
  ),
  fadeModal: T.func.isRequired,

  // deprecated. use props.confirmAction instead.
  confirmButtonText: T.string,
  handleConfirm: T.func
}

ConfirmModal.defaultProps = {
  dangerous: false
}

export {
  ConfirmModal
}
