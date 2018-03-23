import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

import {BaseModal} from './base.jsx'

const ConfirmModal = props =>
  <BaseModal {...props}>
    {props.isHtml &&
      <HtmlText className="modal-body">{props.question}</HtmlText>
    }

    {!props.isHtml &&
      <div className="modal-body">{props.question}</div>
    }

    <button
      className={classes('modal-btn btn', props.dangerous ? 'btn-danger' : 'btn-primary')}
      onClick={() => {
        props.handleConfirm()
        props.fadeModal()
      }}
    >
      {props.confirmButtonText || t('confirm')}
    </button>
  </BaseModal>

ConfirmModal.propTypes = {
  confirmButtonText: T.string,
  dangerous: T.bool,
  question: T.string.isRequired,
  isHtml: T.bool,
  handleConfirm: T.func.isRequired,
  fadeModal: T.func.isRequired
}

ConfirmModal.defaultProps = {
  dangerous: false,
  isHtml: false
}

export {
  ConfirmModal
}
