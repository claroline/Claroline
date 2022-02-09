import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {getCorrectionKey} from '#/plugin/drop-zone/resources/dropzone/utils'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

// TODO : restore list grid display

const Correctors = props =>
  <div id="correctors-list">
    <h2>{trans('correctors_list', {}, 'dropzone')}</h2>
    {!props.corrections ?
      <span className="fa fa-fw fa-circle-o-notch fa-spin" /> :
      <ListData
        name={`${selectors.STORE_NAME}.drops`}
        fetch={{
          url: ['claro_dropzone_drops_search', {id: props.dropzone.id}],
          autoload: true
        }}
        primaryAction={(row) => ({
          type: LINK_BUTTON,
          target: `${props.path}/corrector/${row.id}`,
          label: trans('corrector', {}, 'dropzone')
        })}
        definition={[
          {
            name: 'user',
            label: trans('user', {}, 'platform'),
            displayed: props.dropzone.parameters.dropType === constants.DROP_TYPE_USER,
            displayable: props.dropzone.parameters.dropType === constants.DROP_TYPE_USER,
            primary: true,
            render: (rowData) => rowData.user ? `${rowData.user.firstName} ${rowData.user.lastName}` : trans('unknown')
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
            render: (rowData) => {
              const key = getCorrectionKey(rowData, props.dropzone)

              return props.corrections && props.corrections[key] ? props.corrections[key].length : 0
            }
          }, {
            name: 'nbFinishedCorrections',
            label: trans('finished_corrections', {}, 'dropzone'),
            displayed: true,
            filterable: false,
            sortable: false,
            render: (rowData) => {
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
            render: (rowData) => {
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
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-eye',
            label: trans('open', {}, 'platform'),
            target: `${props.path}/corrector/${rows[0].id}`,
            scope: ['object']
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-unlock',
            label: trans('unlock_corrector', {}, 'dropzone'),
            callback: () => props.unlockUser(rows[0].id),
            scope: ['object']
          }
        ]}
      />
    }
  </div>

Correctors.propTypes = {
  path: T.string.isRequired,
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  drops: T.object,
  corrections: T.oneOfType([T.object, T.array]),
  unlockUser: T.func.isRequired
}

const ConnectedCorrectors = connect(
  state => ({
    dropzone: selectors.dropzone(state),
    drops: selectors.drops(state),
    corrections: selectors.corrections(state)
  }),
  dispatch => ({
    unlockUser: (dropId) => dispatch(actions.unlockDropUser(dropId))
  })
)(Correctors)

export {
  ConnectedCorrectors as Correctors
}
