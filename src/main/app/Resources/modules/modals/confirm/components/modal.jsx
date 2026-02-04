import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import classes from 'classnames'
import invariant from 'invariant'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Html} from '#/main/app/components/html'
import {DataMicro} from '#/main/app/data/components/micro'
import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'
import {CallbackButton} from '#/main/app/buttons/callback'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {registry as buttonRegistry} from '#/main/app/buttons/registry'

/**
 * We don't reuse the standard <Button /> to avoid circular references and because we don't need the full flexibility
 * of it (like confirm modale or tooltips)
 */
const ConfirmButton = (props) => {
  const button = buttonRegistry.get(props.type)

  invariant(undefined !== button, `You have requested a non existent button "${props.type}".`)

  return createElement(button, merge(omit(props, 'type', 'icon', 'label', 'subscript', 'managerOnly', 'description')), props.label)
}

const ConfirmModal = (props) =>
  <ModalEmpty
    {...omit(props, 'dangerous', 'question', 'additional', 'items', 'confirmAction')}
    centered={true}
    size="sm"
    enforceFocus={true}
  >
    <div className="modal-body mt-3" role="presentation">
      <Html className="lead" align="center">
        {props.question || trans('action_confirm_message')}
      </Html>

      {props.items && 0 < props.items.length &&
        <ul className="list-group list-group-flush border-top border-bottom mt-4">
          {props.items.map((item) =>
            <li key={item.id} className="list-group-item px-0">
              <DataMicro object={item} />
            </li>
          )}
        </ul>
      }

      {props.additional &&
        <Html className="text-body-secondary fw-bold mt-4" align="center">
          {props.additional}
        </Html>
      }

      {props.children}
    </div>

    <div className="modal-footer bg-transparent" role="toolbar">
      <CallbackButton
        className="btn btn-body flex-fill"
        callback={props.fadeModal}
      >
        {props.cancel || trans('cancel', {}, 'actions')}
      </CallbackButton>

      <ConfirmButton
        label={trans('confirm', {}, 'actions')}
        {...omit(props.confirmAction, 'icon', 'tooltip', 'size', 'className')}
        className={classes('btn flex-fill', {
          'btn-primary': !props.dangerous,
          'btn-danger': props.dangerous
        })}
        variant="btn"
        onClick={props.fadeModal}
        dangerous={props.dangerous}
        primary={!props.dangerous}
      />
    </div>
  </ModalEmpty>

ConfirmModal.propTypes = {
  dangerous: T.bool,
  question: T.string, // It can be plain text or HTML
  additional: T.string,
  items: T.arrayOf(T.shape({
    thumbnail: T.string,
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  cancel: T.oneOfType([T.bool, T.string]),
  confirmAction: T.shape(
    ActionTypes.propTypes
  ).isRequired,
  children: T.any,

  // from modal,
  fadeModal: T.func.isRequired
}

export {
  ConfirmModal
}
