import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

// TODO : restore list grid display

const DropsList = props =>
  <section className="resource-section drop-list">
    <h2>{trans('corrections_management', {}, 'dropzone')}</h2>

    <ListData
      name="drops"
      fetch={{
        url: ['claro_dropzone_drops_search', {id: props.dropzone.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `/drop/${row.id}`,
        label: trans('correct_a_copy', {}, 'dropzone')
      })}
      definition={[
        {
          name: 'user',
          label: trans('user', {}, 'platform'),
          type: 'string',
          displayed: constants.DROP_TYPE_USER === props.dropzone.parameters.dropType,
          displayable: constants.DROP_TYPE_USER === props.dropzone.parameters.dropType,
          render: (rowData) => rowData.user ? `${rowData.user.firstName} ${rowData.user.lastName}` : trans('unknown')
        }, {
          name: 'teamName',
          label: trans('team', {}, 'team'),
          type: 'string',
          displayed: constants.DROP_TYPE_TEAM === props.dropzone.parameters.dropType,
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
          options: {
            max: props.dropzone.parameters.scoreMax
          }
        }
      ]}
      actions={(rows) => [
        {
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('correct_the_copy', {}, 'dropzone'),
          target: `/drop/${rows[0].id}`,
          scope: ['object']
        },
        {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-unlock',
          label: trans('unlock_drop', {}, 'dropzone'),
          displayed: !rows[0].unlockedDrop,
          callback: () => props.unlockDrop(rows[0].id),
          scope: ['object'] // todo should be selection action too
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-undo',
          label: trans('cancel_drop_submission', {}, 'dropzone'),
          displayed: rows[0].finished,
          callback: () => props.cancelDrop(rows[0].id),
          scope: ['object'] // todo should be selection action too
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('download', {}, 'platform'),
          callback: () => props.downloadDrops(rows)
        }
      ]}
    />
  </section>

DropsList.propTypes = {
  dropzone: T.shape(
    DropzoneType.propTypes
  ).isRequired,
  unlockDrop: T.func.isRequired,
  cancelDrop: T.func.isRequired,
  downloadDrops: T.func.isRequired
}

const Drops = connect(
  (state) => ({
    dropzone: select.dropzone(state)
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
