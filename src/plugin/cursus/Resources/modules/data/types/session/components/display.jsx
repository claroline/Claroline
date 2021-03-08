import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {SessionCard} from '#/plugin/cursus/session/components/card'

const SessionDisplay = (props) => props.data ?
  <SessionCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-calendar-week"
    title={trans('no_session', {}, 'cursus')}
  />

SessionDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    SessionTypes.propTypes
  ))
}

export {
  SessionDisplay
}
