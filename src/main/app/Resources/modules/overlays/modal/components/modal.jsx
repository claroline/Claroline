import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/Modal'

import {asset} from '#/main/app/config/asset'
import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'

const Modal = (props) => {
  const titleId = useId()

  return (
    <ModalEmpty
      {...omit(props, 'closeButton', 'icon', 'title', 'subtitle', 'poster')}
      aria-labelledby={titleId}
    >
      {(props.title || props.icon) &&
        <BaseModal.Header
          closeButton={props.closeButton}
          style={props.poster && {
            backgroundImage: `url("${asset(props.poster)}")`
          }}
          className={classes({
            'modal-poster': !!props.poster
          })}
        >
          {props.icon && typeof props.icon === 'string' &&
            <span className={classes('modal-icon fs-5 text-primary', props.icon)} aria-hidden={true} />
          }

          {typeof props.icon !== 'string' &&
            props.icon
          }

          <BaseModal.Title className="flex-fill h5" as="h1" id={titleId}>
            {props.title}

            {props.subtitle &&
              <small className={classes('fs-base', !props.poster && 'text-body-secondary')}>{props.subtitle}</small>
            }
          </BaseModal.Title>
        </BaseModal.Header>
      }

      {props.children}
    </ModalEmpty>
  )
}

Modal.propTypes = {
  ...ModalEmpty.propTypes,

  closeButton: T.bool,
  /**
   * @deprecated
   */
  poster: T.string,
  icon: T.oneOfType([T.string, T.object]),
  title: T.oneOfType([T.string, T.node]),
  subtitle: T.string
}

Modal.defaultProps = {
  closeButton: true
}

export {
  Modal
}
