import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

// TODO : restore list grid display

const DropsList = props =>
  <section className="resource-section drop-list">
    <h2>{trans('corrections', {}, 'dropzone')}</h2>

    <ListData
      name={`${selectors.STORE_NAME}.drops`}
      fetch={{
        url: ['claro_dropzone_drops_search', {id: props.dropzone.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/drop/${row.id}`,
        label: trans('correct_a_copy', {}, 'dropzone')
      })}
      delete={{
        url: ['claro_dropzone_drop_delete', {id: props.dropzone.id}],
        displayed: () => !get(props.dropzone, 'restrictions.lockDrops', false) && props.canDelete
      }}
      definition={[
        {
          name: 'user',
          label: trans('user'),
          type: 'string',
          displayed: constants.DROP_TYPE_USER === props.dropzone.parameters.dropType,
          filterable: constants.DROP_TYPE_USER === props.dropzone.parameters.dropType,
          displayable: constants.DROP_TYPE_USER === props.dropzone.parameters.dropType,
          render: (rowData) => rowData.user ? `${rowData.user.firstName} ${rowData.user.lastName}` : trans('unknown')
        }, {
          name: 'teamName',
          label: trans('team', {}, 'team'),
          type: 'string',
          displayed: constants.DROP_TYPE_TEAM === props.dropzone.parameters.dropType,
          filterable: constants.DROP_TYPE_TEAM === props.dropzone.parameters.dropType,
          displayable: constants.DROP_TYPE_TEAM === props.dropzone.parameters.dropType
        }, {
          name: 'dropDate',
          label: trans('drop_date', {}, 'dropzone'),
          type: 'date',
          displayed: true,
          filterable: true,
          options: {
            time: true
          }
        }, {
          name: 'finished',
          label: trans('submitted', {}, 'dropzone'),
          type: 'boolean'
        }, {
          name: 'unlockedDrop',
          label: trans('unlocked', {}, 'dropzone'),
          type: 'boolean',
          displayed: true
        }, {
          name: 'autoClosedDrop',
          label: trans('incomplete', {}, 'platform'),
          type: 'boolean',
          displayed: true
        }, {
          name: 'evaluated',
          label: trans('evaluated', {}, 'dropzone'),
          type: 'boolean',
          calculated: (rowData) => {
            const nbExpectedCorrections = constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType ? props.dropzone.parameters.expectedCorrectionTotal : 1
            const nbValidCorrections = rowData.corrections.filter(c => c.finished && c.valid).length

            return nbValidCorrections >= nbExpectedCorrections
          },
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'score',
          label: trans('score', {}, 'platform'),
          type: 'score',
          displayed: true,
          calculated: (row) => ({
            current: row.score,
            total: props.dropzone.parameters.scoreMax
          })
        }, {
          name: 'userDisabled',
          label: trans('user_disabled'),
          type: 'boolean',
          displayable: false,
          sortable: false,
          filterable: true
        }
      ]}
      actions={(rows) => [
        {
          name: 'correct',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('correct_the_copy', {}, 'dropzone'),
          target: `${props.path}/drop/${rows[0].id}`,
          disabled: !rows[0].finished,
          scope: ['object'],
          primary: true
        }, {
          name: 'unlock',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-unlock',
          label: trans('unlock_drop', {}, 'dropzone'),
          displayed: !rows[0].unlockedDrop,
          callback: () => props.unlockDrop(rows[0].id),
          scope: ['object'], // todo should be selection action too
          group: trans('management')
        }, {
          name: 'cancel-submission',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-undo',
          label: trans('cancel_drop_submission', {}, 'dropzone'),
          displayed: rows[0].finished,
          callback: () => props.cancelDrop(rows[0].id),
          scope: ['object'], // todo should be selection action too
          group: trans('management')
        }, {
          name: 'download',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('download', {}, 'platform'),
          callback: () => props.downloadDrops(rows),
          group: trans('transfer')
        }
      ]}
    />
  </section>

DropsList.propTypes = {
  path: T.string.isRequired,
  dropzone: T.shape(
    DropzoneType.propTypes
  ).isRequired,
  canDelete: T.bool.isRequired,
  unlockDrop: T.func.isRequired,
  cancelDrop: T.func.isRequired,
  downloadDrops: T.func.isRequired
}

const Drops = connect(
  (state) => ({
    dropzone: selectors.dropzone(state),
    canDelete: hasPermission('edit', resourceSelectors.resourceNode(state))
  }),
  (dispatch) => ({
    unlockDrop: (dropId) => dispatch(actions.unlockDrop(dropId)),
    cancelDrop: (dropId) => dispatch(actions.cancelDropSubmission(dropId)),
    downloadDrops: (drops) => dispatch(actions.downloadDrops(drops))
  })
)(DropsList)

export {
  Drops
}
