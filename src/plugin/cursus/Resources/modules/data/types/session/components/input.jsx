import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {EntityInput} from '#/main/app/data/types/entity'

import {Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {MODAL_TRAINING_SESSIONS} from '#/plugin/cursus/modals/sessions'

const SessionInput = props =>
  <EntityInput
    {...props}
    pickerType={MODAL_TRAINING_SESSIONS}
    add={trans(props.multiple ? 'add_training_sessions' : 'add_training_session', {}, 'actions')}
  />

implementPropTypes(SessionInput, EntityInput.propTypes, {
  value: T.oneOfType([
    T.shape(
      SessionTypes.propTypes
    ),
    T.arrayOf(T.shape(
      SessionTypes.propTypes
    ))
  ])
})

export {
  SessionInput
}
