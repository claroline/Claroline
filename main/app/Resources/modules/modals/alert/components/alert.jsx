import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlay/modal/components/modal'

const AlertModal = props =>
  <Modal
    {...omit(props, 'type', 'message')}
    icon={classes('fa fa-fw', {
      'fa-info-circle':          props.type === 'info',
      'fa-check-circle':         props.type === 'success',
      'fa-exclamation-triangle': props.type === 'warning',
      'fa-minus-circle':         props.type === 'danger'
    })}
  >
    <div className="modal-body">
      {props.message}
    </div>
  </Modal>

AlertModal.propTypes = {
  type: T.oneOf([
    'info',
    'warning',
    'success',
    'danger'
  ]).isRequired,
  message: T.string.isRequired
}

AlertModal.defaultProps = {
  type: 'info'
}

export {
  AlertModal
}
