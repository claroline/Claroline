import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {SessionCard} from '#/plugin/cursus/session/components/card'
import {Session as SessionTypes} from '#/plugin/cursus/prop-types'

import {selectors} from '#/plugin/cursus/modals/sessions/store'

const SessionsModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'reset')}
      icon="fa fa-fw fa-calendar-week"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={props.reset}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={[
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            displayed: true
          }
        ]}
        card={SessionCard}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

SessionsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(SessionTypes.propTypes)).isRequired,
  reset: T.func.isRequired
}

SessionsModal.defaultProps = {
  url: ['apiv2_cursus_session_list'],
  title: trans('sessions', {}, 'cursus')
}

export {
  SessionsModal
}
