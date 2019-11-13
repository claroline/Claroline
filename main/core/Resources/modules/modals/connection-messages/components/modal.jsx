import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/main/core/administration/parameters/constants'
import {selectors} from '#/main/core/modals/connection-messages/store'

const ConnectionMessagesModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'reset')}
      icon="fa fa-fw fa-comment-dots"
      className="data-picker-modal"
      bsSize="lg"
      onExited={props.reset}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={[
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            displayed: true,
            primary: true
          }, {
            name: 'type',
            type: 'choice',
            label: trans('type'),
            options: {
              choices: constants.MESSAGE_TYPES
            },
            displayed: true
          }, {
            name: 'restrictions.dates[0]',
            alias: 'startDate',
            type: 'date',
            label: trans('start_date'),
            displayed: true
          }, {
            name: 'restrictions.dates[1]',
            alias: 'endDate',
            type: 'date',
            label: trans('end_date'),
            displayed: true
          }, {
            name: 'restrictions.roles',
            type: 'roles',
            label: trans('roles'),
            displayed: true,
            filterable: false
          }, {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('hidden')
          }
        ]}
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

ConnectionMessagesModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape({})).isRequired,
  reset: T.func.isRequired
}

ConnectionMessagesModal.defaultProps = {
  url: ['apiv2_connectionmessage_list'],
  title: trans('connection_messages')
}

export {
  ConnectionMessagesModal
}
