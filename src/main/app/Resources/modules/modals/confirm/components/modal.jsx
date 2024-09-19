import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'
import {Thumbnail} from '#/main/app/components/thumbnail'

const ConfirmModal = (props) =>
  <ModalEmpty
    {...omit(props, 'dangerous', 'question', 'additional', 'items', 'confirmAction', 'handleConfirm')}
    centered={true}
    size="sm"
  >
    <div className="modal-body mt-3" role="presentation">
      <ContentHtml className="lead" align="center">
        {props.question || trans('action_confirm_message')}
      </ContentHtml>

      {props.items && 1 < props.items.length &&
        <ul className="list-group mt-4">
          {props.items.map((item) =>
            <li className="list-group-item d-flex align-items-center">
              <Thumbnail
                className="me-3"
                size="xs"
                thumbnail={item.thumbnail}
                name={item.name}
                square={true}
              />
              <span className="text-truncate">{item.name}</span>
            </li>
          )}
        </ul>
      }

      {props.additional &&
        <ContentHtml className="text-body-secondary fw-bold mt-4" align="center">
          {props.additional}
        </ContentHtml>
      }

      {props.children}
    </div>

    <div className="modal-footer bg-transparent">
      {props.cancel &&
        <Button
          className="btn btn-body flex-fill"
          label={props.cancel}
          type={CALLBACK_BUTTON}
          callback={props.fadeModal}
        />
      }

      {props.confirmAction ?
        <Button
          label={trans('confirm', {}, 'actions')}
          {...omit(props.confirmAction, 'icon', 'tooltip', 'size', 'className')}
          className="flex-fill"
          variant="btn"
          onClick={props.fadeModal}
          dangerous={props.dangerous}
          primary={!props.dangerous}
        /> :
        <Button
          type={CALLBACK_BUTTON}
          label={props.confirmButtonText || trans('confirm', {}, 'actions')}
          variant="btn"
          className="flex-fill"
          callback={() => {
            props.handleConfirm()
            props.fadeModal()
          }}
          dangerous={props.dangerous}
          primary={!props.dangerous}
        />
      }
    </div>
  </ModalEmpty>

ConfirmModal.propTypes = {
  dangerous: T.bool,
  question: T.string.isRequired, // It can be plain text or HTML
  additional: T.string,
  items: T.arrayOf(T.shape({
    thumbnail: T.string,
    name: T.string.isRequired
  })),
  cancel: T.oneOfType([T.bool, T.string]),
  confirmAction: T.shape(
    ActionTypes.propTypes
  ),
  children: T.any,
  // from modal,
  fadeModal: T.func.isRequired,

  // deprecated. use props.confirmAction instead.
  confirmButtonText: T.string,
  handleConfirm: T.func
}

ConfirmModal.defaultProps = {
  dangerous: false,
  items: [],
  cancel: trans('cancel', {}, 'actions')
}

export {
  ConfirmModal
}
