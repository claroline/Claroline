import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {convertTimestampToString} from '#/main/app/intl/date'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'
import {MODAL_USER_MESSAGE} from '#/main/core/user/modals/message'

import {selectors} from '#/plugin/analytics/tools/dashboard/path/modals/participants/store'

const ParticipantsModal = props =>
  <Modal
    {...omit(props, 'reset', 'resourceNode')}
    icon="fa fa-fw fa-user"
    title={trans('participants')}
    subtitle={props.resourceNode.name}
    className="data-picker-modal"
    bsSize="lg"
    onExited={props.reset}
  >
    <ListData
      name={selectors.STORE_NAME}
      fetch={{
        url: ['claroline_path_evaluations_list', {resourceNode: props.resourceNode.id}],
        autoload: true
      }}
      actions={(rows) => [
        {
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-envelope',
          label: trans('send_message'),
          scope: ['object', 'collection'],
          modal: [MODAL_USER_MESSAGE, {
            to: rows.map((row) => ({
              id: row.user.id,
              name: `${row.user.firstName} ${row.user.lastName}`
            }))
          }]
        }
      ]}
      definition={[
        {
          name: 'user',
          type: 'user',
          label: trans('user'),
          displayed: true
        }, {
          name: 'user.firstName',
          type: 'string',
          label: trans('first_name'),
          displayable: false,
          displayed: false
        }, {
          name: 'user.lastName',
          type: 'string',
          label: trans('last_name'),
          displayable: false,
          displayed: false
        }, {
          name: 'progression',
          type: 'number',
          label: trans('progression'),
          displayed: true,
          render: (rowData) => rowData.progression + ' / ' + rowData.progressionMax
        }, {
          name: 'score',
          type: 'number',
          label: trans('score'),
          displayed: true,
          render: (rowData) => {
            if (rowData.scoreMax) {
              return (rowData.score) + ' / ' + rowData.scoreMax
            }

            return '-'
          }
        }, {
          name: 'duration',
          type: 'number',
          label: trans('duration'),
          displayed: true,
          filterable: false,
          calculated: (rowData) => rowData.duration !== null ? convertTimestampToString(rowData.duration) : null
        }, {
          name: 'date',
          type: 'date',
          label: trans('last_activity'),
          displayed: true,
          options: {
            time: true
          }
        }
      ]}
    />
  </Modal>

ParticipantsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  resourceNode: T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  }),
  fadeModal: T.func.isRequired,
  reset: T.func.isRequired
}

ParticipantsModal.defaultProps = {

}

export {
  ParticipantsModal
}
