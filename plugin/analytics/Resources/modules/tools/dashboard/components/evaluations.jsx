import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from  '#/main/core/tool/store'

import {selectors} from '#/plugin/analytics/tools/dashboard/store'

const EvaluationsComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.evaluations'}
    fetch={{
      url: ['apiv2_workspace_evaluations_list', {workspace: props.workspaceId}],
      autoload: true
    }}
    definition={[
      {
        name: 'userName',
        type: 'string',
        label: trans('user'),
        displayed: true,
        primary: true
      }, {
        name: 'progression',
        type: 'number',
        label: trans('progression'),
        displayed: true,
        calculated: (row) => row.progressionMax ? `${row.progression}/${row.progressionMax}` : '-'
      }
    ]}
  />

EvaluationsComponent.propTypes = {
  workspaceId: T.string.isRequired
}

const Evaluations = connect(
  state => ({
    workspaceId: toolSelectors.contextData(state).uuid
  })
)(EvaluationsComponent)

export {
  Evaluations
}
