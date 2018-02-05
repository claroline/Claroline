import React, {Component} from 'react'
import moment from 'moment'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {generateUrl} from '#/main/core/api/router'
import {navigate} from '#/main/core/router'
import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

class Drops extends Component {
  generateColumns(props) {
    const columns = []

    columns.push({
      name: 'dropDate',
      label: trans('submit_date', {}, 'dropzone'),
      displayed: true,
      displayable: true,
      filterable: true,
      type: 'date',
      renderer: (rowData) => {
        const element = <a href={`#/drop/${rowData.id}`}>
          {rowData.dropDate ? moment(rowData.dropDate).format('YYYY-MM-DD') : '-'}
        </a>

        return element
      }
    })

    if (props.dropzone.parameters.dropType === constants.DROP_TYPE_USER) {
      columns.push({
        name: 'user',
        label: trans('user', {}, 'platform'),
        displayed: true,
        renderer: (rowData) => rowData.user ? `${rowData.user.firstName} ${rowData.user.lastName}` : '-'
      })
    }
    if (props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM) {
      columns.push({
        name: 'teamName',
        label: trans('team', {}, 'team'),
        displayed: true,
        type: 'string'
      })
    }
    columns.push({
      name: 'score',
      label: trans('score', {}, 'platform'),
      displayed: true,
      renderer: (rowData) => rowData.score !== null ? `${rowData.score} / ${props.dropzone.parameters.scoreMax}` : ''
    })
    columns.push({
      name: 'finished',
      label: trans('submitted', {}, 'dropzone'),
      displayed: true,
      type: 'boolean'
    })
    columns.push({
      name: 'complete',
      label: trans('complete', {}, 'dropzone'),
      displayed: true,
      filterable: false,
      sortable: false,
      type: 'boolean',
      renderer: (rowData) => {
        const nbExpectedCorrections = constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType ? props.dropzone.parameters.expectedCorrectionTotal : 1
        const nbValidCorrections = rowData.corrections.filter(c => c.finished && c.valid).length
        const element = nbValidCorrections >= nbExpectedCorrections ?
          <span className="fa fa-fw fa-check true"/> :
          <span className="fa fa-fw fa-times false"/>

        return element
      }
    })
    columns.push({
      name: 'unlockedDrop',
      label: trans('unlocked', {}, 'dropzone'),
      displayed: true,
      type: 'boolean'
    })
    columns.push({
      name: 'autoClosedDrop',
      label: trans('incomplete', {}, 'platform'),
      displayed: true,
      type: 'boolean'
    })

    return columns
  }

  generateActions(props) {
    const actions = []
    actions.push({
      icon: 'fa fa-fw fa-eye',
      label: trans('open', {}, 'platform'),
      action: (rows) => navigate(`/drop/${rows[0].id}`),
      context: 'row'
    })
    actions.push({
      icon: 'fa fa-fw fa-unlock',
      label: trans('unlock_drop', {}, 'dropzone'),
      displayed: (rows) => !rows[0].unlockedDrop,
      action: (rows) => props.unlockDrop(rows[0].id),
      context: 'row'
    })
    actions.push({
      icon: 'fa fa-fw fa-undo',
      label: trans('cancel_drop_submission', {}, 'dropzone'),
      displayed: (rows) => rows[0].finished,
      action: (rows) => props.cancelDropSubmission(rows[0].id),
      context: 'row'
    })

    return actions
  }

  render() {
    return (
      <div id="corrections-management">
        <h2>{trans('corrections_management', {}, 'dropzone')}</h2>
        <DataListContainer
          name="drops"
          fetch={{
            url: generateUrl('claro_dropzone_drops_search', {id: this.props.dropzone.id}),
            autoload: true
          }}
          definition={this.generateColumns(this.props)}
          filterColumns={true}
          actions={this.generateActions(this.props)}
          card={(row) => ({
            onClick: `#/drop/${row.id}/view`,
            poster: null,
            icon: null,
            title: '',
            subtitle: ''
          })}
        />
      </div>
    )
  }
}

Drops.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  drops: T.object,
  unlockDrop: T.func.isRequired,
  cancelDropSubmission: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    dropzone: select.dropzone(state),
    drops: select.drops(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    unlockDrop: (dropId) => dispatch(actions.unlockDrop(dropId)),
    cancelDropSubmission: (dropId) => dispatch(actions.cancelDropSubmission(dropId))
  }
}

const ConnectedDrops = connect(mapStateToProps, mapDispatchToProps)(Drops)

export {ConnectedDrops as Drops}