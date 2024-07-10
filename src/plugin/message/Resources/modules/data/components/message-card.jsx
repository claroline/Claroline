import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {getPlainText} from '#/main/app/data/types/html/utils'
import {DataCard} from '#/main/app/data/components/card'

import {Message as MessageTypes} from '#/plugin/message/prop-types'

const MessageCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'meta.read', false)
    })}
    poster={get(props.data, 'from.picture')}
    icon={!get(props.data, 'from.picture') ? <>{props.data.from.name.charAt(0)}</> : null}
    asIcon={true}
    title={props.data.object || trans('no_object', {}, 'message')}
    contentText={getPlainText(props.data.content)}
    meta={trans('sent_at', {
      date: displayDate(get(props.data, 'meta.date'), false, true)
    }, 'message')}
  />

MessageCard.propTypes = {
  className: T.string,
  data: T.shape(
    MessageTypes.propTypes
  ).isRequired
}

export {
  MessageCard
}
