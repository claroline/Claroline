import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/lib/Modal'

const Modal = props =>
  <BaseModal
    {...omit(props, 'fadeModal', 'hideModal', 'icon', 'title', 'subtitle', 'className', 'children')}
    autoFocus={true}
    onHide={props.fadeModal}
    onExited={props.hideModal}
    dialogClassName={props.className}
  >
    {props.title &&
      <BaseModal.Header closeButton>
        <BaseModal.Title>
          {props.icon &&
            <span className={classes('modal-icon', props.icon)} />
          }

          {props.title}

          {props.subtitle &&
            <small className={classes({'with-icon': !!props.icon})}>{props.subtitle}</small>
          }
        </BaseModal.Title>
      </BaseModal.Header>
    }

    {props.children}
  </BaseModal>

Modal.propTypes = {
  bsSize: T.string,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  show: T.bool.isRequired,

  icon: T.string,
  title: T.string,
  subtitle: T.string,
  className: T.string,
  children: T.node.isRequired
}

// required when testing prop-types on code instrumented by istanbul
// @see https://github.com/facebook/jest/issues/1824#issuecomment-250478026
Modal.displayName = 'Modal'

export {
  Modal
}
