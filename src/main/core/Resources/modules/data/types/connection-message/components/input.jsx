import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {route} from '#/main/core/administration/routing'

import {ConnectionMessage as ConnectionMessageTypes} from '#/main/core/data/types/connection-message/prop-types'
import {ConnectionMessageCard} from '#/main/core/data/types/connection-message/components/card'
import {MODAL_CONNECTION_MESSAGES} from '#/main/core/modals/connection-messages'

const ConnectionMessageButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_connection_message')}
    disabled={props.disabled}
    modal={[MODAL_CONNECTION_MESSAGES, {
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

ConnectionMessageButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const ConnectionMessageInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <ConnectionMessageCard
          data={props.value}
          size="xs"
          primaryAction={{
            type: LINK_BUTTON,
            label: trans('open', {}, 'actions'),
            target: route('main_settings')+'/messages/form/'+props.value.id
          }}
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <ConnectionMessageButton
          {...props.picker}
          disabled={props.disabled}
          onChange={props.onChange}
          size={props.size}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-comment-dots"
      title={trans('no_connection_message')}
      size={props.size}
    >
      <ConnectionMessageButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(ConnectionMessageInput, DataInputTypes, {
  value: T.shape(
    ConnectionMessageTypes.propTypes
  )
}, {
  value: null
})

export {
  ConnectionMessageInput
}
