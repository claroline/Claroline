import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {ContentHtml} from '#/main/app/content/components/html'
import {Modal} from '#/main/app/overlays/modal/components/modal'

const AlertModal = props =>
  <Modal
    {...omit(props, 'type', 'message')}
    size="md"
    icon={classes('fa fa-fw', {
      'fa-circle-info':          props.type === 'info',
      'fa-circle-check':         props.type === 'success',
      'fa-exclamation-triangle': props.type === 'warning',
      'fa-minus-circle':         props.type === 'danger'
    })}
    title={props.title}
  >
    <ContentHtml className="modal-body">
      {props.message}
    </ContentHtml>
  </Modal>

AlertModal.propTypes = {
  type: T.oneOf([
    'info',
    'warning',
    'success',
    'danger'
  ]).isRequired,
  title: T.string,
  message: T.string.isRequired
}

AlertModal.defaultProps = {
  type: 'info'
}

export {
  AlertModal
}
