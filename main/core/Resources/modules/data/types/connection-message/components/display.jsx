import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EmptyPlaceholder} from '#/main/app/content/components/placeholder'
import {route} from '#/main/core/administration/routing'

import {ConnectionMessage as ConnectionMessageTypes} from '#/main/core/data/types/connection-message/prop-types'
import {ConnectionMessageCard} from '#/main/core/data/types/connection-message/components/card'

const ConnectionMessageDisplay = (props) => !isEmpty(props.data) ?
  <ConnectionMessageCard
    key={`connection-message-card-${props.data.id}`}
    data={props.data}
    size="xs"
    primaryAction={{
      type: LINK_BUTTON,
      label: trans('open', {}, 'actions'),
      target: route('main_settings')+'/messages/form/'+props.data.id
    }}
  /> :
  <EmptyPlaceholder
    icon="fa fa-comment-dots"
    title={trans('no_connection_message')}
  />

ConnectionMessageDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    ConnectionMessageTypes.propTypes
  ))
}

export {
  ConnectionMessageDisplay
}
