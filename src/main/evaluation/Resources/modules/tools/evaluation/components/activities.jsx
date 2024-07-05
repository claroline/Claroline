import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ToolPage} from '#/main/core/tool'
import {Alert} from '#/main/app/components/alert'
import {ResourceList} from '#/main/core/resource/components/list'
import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {constants as listConst} from '#/main/app/content/list/constants'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

const EvaluationActivities = (props) =>
  <ToolPage
    title={trans('activities')}
    primaryAction="add-resource"
    actions={[
      {
        name: 'add-resource',
        type: MODAL_BUTTON,
        label: trans('add_resources'),
        modal: [MODAL_RESOURCES, {
          contextId: props.contextId,
          selectAction: (selected) => ({
            type: CALLBACK_BUTTON,
            callback: () => props.addRequiredResources(props.contextId, selected)
          })
        }]
      }
    ]}
  >
    <Alert
      className="mt-3"
      type="info"
      title={trans('workspace_requirements_help_title', {}, 'evaluation')}
    >
      {trans('workspace_requirements_help_description', {}, 'evaluation')}
    </Alert>

    <ResourceList
      className="mb-3"
      name={selectors.STORE_NAME+'.requiredResources'}
      url={['apiv2_workspace_required_resource_list', {workspace: props.contextId}]}
      delete={{
        url: ['apiv2_workspace_required_resource_remove', {workspace: props.contextId}]
      }}
      actions={undefined}
      display={{
        current: listConst.DISPLAY_LIST_SM
      }}
    />
  </ToolPage>

EvaluationActivities.propTypes = {
  contextId: T.string.isRequired,
  addRequiredResources: T.func.isRequired
}

export {
  EvaluationActivities
}
