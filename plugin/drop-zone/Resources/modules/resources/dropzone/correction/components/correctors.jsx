import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {generateUrl} from '#/main/core/api/router'
import {navigate} from '#/main/core/router'
import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {getCorrectionKey} from '#/plugin/drop-zone/resources/dropzone/utils'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

// TODO : restore list grid display

class Correctors extends Component {
  generateColumns(props) {
    const columns = []

    if (props.dropzone.parameters.dropType === constants.DROP_TYPE_USER) {
      columns.push({
        name: 'user',
        label: trans('user', {}, 'platform'),
        displayed: true,
        primary: true
      })
    }
    if (props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM) {
      columns.push({
        name: 'teamName',
        label: trans('team', {}, 'team'),
        displayed: true,
        primary: true
      })
    }

    columns.push({
      name: 'nbCorrections',
      label: trans('started_corrections', {}, 'dropzone'),
      displayed: true,
      filterable: false,
      sortable: false,
      renderer: (rowData) => {
        const key = getCorrectionKey(rowData, props.dropzone)

        return props.corrections && props.corrections[key] ? props.corrections[key].length : 0
      }
    })
    columns.push({
      name: 'nbFinishedCorrections',
      label: trans('finished_corrections', {}, 'dropzone'),
      displayed: true,
      filterable: false,
      sortable: false,
      renderer: (rowData) => {
        const nbExpectedCorrections = props.dropzone.parameters.expectedCorrectionTotal
        const key = getCorrectionKey(rowData, props.dropzone)
        const nbCorrections = props.corrections && props.corrections[key] ?
          props.corrections[key].filter(c => c.finished).length :
          0

        return `${nbCorrections} / ${nbExpectedCorrections}`
      }
    })
    columns.push({
      name: 'nbDeniedCorrections',
      label: trans('denied_corrections', {}, 'dropzone'),
      displayed: true,
      filterable: false,
      sortable: false,
      renderer: (rowData) => {
        const key = getCorrectionKey(rowData, props.dropzone)

        return props.corrections && props.corrections[key] ?
          props.corrections[key].filter(c => c.correctionDenied).length :
          0
      }
    })
    columns.push({
      name: 'unlockedUser',
      label: trans('unlocked', {}, 'dropzone'),
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
      action: (rows) => navigate(`/corrector/${rows[0].id}`),
      context: 'row'
    })
    actions.push({
      icon: 'fa fa-fw fa-unlock',
      label: trans('unlock_corrector', {}, 'dropzone'),
      action: (rows) => props.unlockUser(rows[0].id),
      context: 'row'
    })

    return actions
  }

  render() {
    return (
      <div id="correctors-list">
        <h2>{trans('correctors_list', {}, 'dropzone')}</h2>
        {!this.props.corrections ?
          <span className="fa fa-fw fa-circle-o-notch fa-spin" /> :
          <DataListContainer
            name="drops"
            fetch={{
              url: generateUrl('claro_dropzone_drops_search', {id: this.props.dropzone.id}),
              autoload: true
            }}
            open={{
              action: (row) => `#/corrector/${row.id}`
            }}
            definition={this.generateColumns(this.props)}
            filterColumns={true}
            actions={this.generateActions(this.props)}
          />
        }
      </div>
    )
  }
}

Correctors.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  drops: T.object,
  corrections: T.oneOfType([T.object, T.array]),
  unlockUser: T.func.isRequired
}

const ConnectedCorrectors = connect(
  state => ({
    dropzone: select.dropzone(state),
    drops: select.drops(state),
    corrections: select.corrections(state)
  }),
  dispatch => ({
    unlockUser: (dropId) => dispatch(actions.unlockDropUser(dropId))
  })
)(Correctors)

export {
  ConnectedCorrectors as Correctors
}
