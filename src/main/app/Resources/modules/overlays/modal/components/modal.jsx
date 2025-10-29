import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import CloseButton from 'react-bootstrap/CloseButton'
import BaseModal from 'react-bootstrap/Modal'
import classes from 'classnames'
import omit from 'lodash/omit'

import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'
import {Poster} from '#/main/app/components/poster'
import {Toolbar} from '#/main/app/action'

const ModalActions = ({fadeModal, className, close, toolbar, actions = []}) => {
  return (
    <div className={classes('modal-toolbar d-flex flex-row align-items-center gap-3', className)}>
      <Toolbar
        className="d-flex flex-row gap-1"
        buttonName="btn btn-text-body btn-modal rounded-circle bg-body focus-ring d-inline-flex align-items-center justify-content-center"
        toolbar={toolbar}
        actions={actions}
        tooltip="bottom"
      />

      {close &&
        <div className="rounded-pill bg-body" role="presentation">
          <CloseButton onClick={fadeModal} className="rounded-circle btn-modal" />
        </div>
      }
    </div>
  )
}

const Modal = (props) => {
  const titleId = useId()

  return (
    <ModalEmpty
      {...omit(props, 'closeButton', 'icon', 'title', 'subtitle', 'poster')}
      aria-labelledby={titleId}
    >
      {props.poster &&
        <div className="position-relative" role="presentation">
          <Poster url={props.poster} className="modal-poster z-0" />
          <ModalActions
            className="position-absolute top-0 end-0 p-4"
            close={props.closeButton}
            toolbar={props.toolbar}
            actions={props.actions}
            fadeModal={props.fadeModal}
          />
        </div>
      }

      {(props.title || props.icon) &&
        <BaseModal.Header
          closeButton={false}
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

          {!props.poster &&
            <ModalActions
              close={props.closeButton}
              toolbar={props.toolbar}
              actions={props.actions}
              fadeModal={props.fadeModal}
            />
          }
        </BaseModal.Header>
      }

      {props.children}
    </ModalEmpty>
  )
}

Modal.propTypes = {
  ...ModalEmpty.propTypes,

  closeButton: T.bool,
  poster: T.string,
  icon: T.oneOfType([T.string, T.object]),
  title: T.oneOfType([T.string, T.node]),
  subtitle: T.string,
  toolbar: T.string,
  actions: T.array
}

Modal.defaultProps = {
  closeButton: true
}

export {
  Modal
}
