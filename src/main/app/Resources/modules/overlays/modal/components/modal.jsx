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
          'modal-poster': !!props.poster
        })}
      >
        <BaseModal.Title className="h-title" as="h5">
          {props.icon &&
            <span className={classes('modal-icon', props.icon)} aria-hidden={true} />
          }

          <div role="presentation">
            {props.title}

            {props.subtitle &&
              <small className={!props.poster && 'text-secondary'}>{props.subtitle}</small>
            }
          </div>
        </BaseModal.Title>
      </BaseModal.Header>
    }

    {props.children}
  </ModalEmpty>

Modal.propTypes = {
  ...ModalEmpty.propTypes,

  closeButton: T.bool,
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
