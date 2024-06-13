import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/Modal'

import {asset} from '#/main/app/config/asset'
import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'

const Modal = (props) =>
  <ModalEmpty
    {...omit(props, 'closeButton', 'icon', 'title', 'subtitle', 'poster')}
  >
    {(props.title || props.icon) &&
      <BaseModal.Header
        closeButton={props.closeButton}
        style={props.poster && {
          backgroundImage: `url("${asset(props.poster)}")`
        }}
        className={classes({
          'modal-poster': !!props.poster,
          'mt-3': !props.poster
        })}
      >
        {props.icon &&
          <span className={classes('modal-icon fs-5', props.icon)} aria-hidden={true} />
        }
        <BaseModal.Title className="flex-fill" as="h5">
          {props.title}

          {props.subtitle &&
            <small className={!props.poster && 'text-body-secondary'}>{props.subtitle}</small>
          }
        </BaseModal.Title>
      </BaseModal.Header>
    }

    {props.children}
  </ModalEmpty>

Modal.propTypes = {
  ...ModalEmpty.propTypes,

  closeButton: T.bool,
  /**
   * @deprecated
   */
  poster: T.string,
  icon: T.string,
  title: T.string,
  subtitle: T.string
}

Modal.defaultProps = {
  closeButton: true
}

export {
  Modal
}
