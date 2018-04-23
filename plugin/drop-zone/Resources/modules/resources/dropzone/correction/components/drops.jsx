import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

// TODO : restore list grid display

const DropsList = props =>
  <section className="resource-section">
    <h2>{trans('corrections_management', {}, 'dropzone')}</h2>

    <DataListContainer
      name="drops"
      fetch={{
        url: ['claro_dropzone_drops_search', {id: props.dropzone.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: 'link',
        target: `#/drop/${row.id}`
      })}
      definition={[
        {
          name: 'user',
          label: trans('user', {}, 'platform'),
          type: 'string',
          displayed: constants.DROP_TYPE_USER === props.dropzone.parameters.dropType,
          displayable: constants.DROP_TYPE_USER === props.dropzone.parameters.dropType,
          renderer: (rowData) => rowData.user ? `${rowData.user.firstName} ${rowData.user.lastName}` : trans('unknown')
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
          name: 'evaluated',
          label: trans('evaluated', {}, 'dropzone'),
          type: 'boolean',
          computed: (rowData) => {
            const nbExpectedCorrections = constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType ? props.dropzone.parameters.expectedCorrectionTotal : 1
            const nbValidCorrections = rowData.corrections.filter(c => c.finished && c.valid).length

            return nbValidCorrections >= nbExpectedCorrections
          },
          displayed: true,
          filterable: false,
          sortable: false
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
          type: 'callback',
          icon: 'fa fa-fw fa-unlock',
          label: trans('unlock_drop', {}, 'dropzone'),
          displayed: !rows[0].unlockedDrop,
          callback: () => props.unlockDrop(rows[0].id),
          context: 'row' // todo should be selection action too
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-undo',
          label: trans('cancel_drop_submission', {}, 'dropzone'),
          displayed: rows[0].finished,
          callback: (rows) => props.cancelDrop(rows[0].id),
          context: 'row' // todo should be selection action too
        }, {
          type: 'callback',
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
