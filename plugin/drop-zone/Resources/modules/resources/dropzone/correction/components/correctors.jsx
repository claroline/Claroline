import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {getCorrectionKey} from '#/plugin/drop-zone/resources/dropzone/utils'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

// TODO : restore list grid display

const Correctors = props =>
  <div id="correctors-list">
    <h2>{trans('correctors_list', {}, 'dropzone')}</h2>
    {!props.corrections ?
      <span className="fa fa-fw fa-circle-o-notch fa-spin" /> :
      <DataListContainer
        name="drops"
        fetch={{
          url: ['claro_dropzone_drops_search', {id: props.dropzone.id}],
          autoload: true
        }}
        primaryAction={(row) => ({
          type: 'link',
          target: `/corrector/${row.id}`
        })}
        definition={[
          {
            name: 'user',
            label: trans('user', {}, 'platform'),
            displayed: props.dropzone.parameters.dropType === constants.DROP_TYPE_USER,
            displayable: props.dropzone.parameters.dropType === constants.DROP_TYPE_USER,
            primary: true
          }, {
            name: 'teamName',
            label: trans('team', {}, 'team'),
            displayed: props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM,
            displayable: props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM,
            primary: true
          }, {
            name: 'nbCorrections',
            label: trans('started_corrections', {}, 'dropzone'),
            displayed: true,
            filterable: false,
            sortable: false,
            renderer: (rowData) => {
              const key = getCorrectionKey(rowData, props.dropzone)

              return props.corrections && props.corrections[key] ? props.corrections[key].length : 0
            }
          }, {
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
          }, {
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
          }, {
            name: 'unlockedUser',
            label: trans('unlocked', {}, 'dropzone'),
            displayed: true,
            type: 'boolean'
          }
        ]}
        filterColumns={true}
        actions={(rows) => [
          {
            type: 'link',
            icon: 'fa fa-fw fa-eye',
            label: trans('open', {}, 'platform'),
            target: `/corrector/${rows[0].id}`,
            context: 'row'
          }, {
            type: 'callback',
            icon: 'fa fa-fw fa-unlock',
            label: trans('unlock_corrector', {}, 'dropzone'),
            callback: () => props.unlockUser(rows[0].id),
            context: 'row'
          }
        ]}
      />
    }
  </div>

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
