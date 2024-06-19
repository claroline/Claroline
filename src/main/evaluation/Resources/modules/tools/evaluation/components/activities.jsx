import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ToolPage} from '#/main/core/tool'
import {Alert} from '#/main/app/components/alert'
import {ResourceList} from '#/main/core/resource/components/list'
import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {constants as listConst} from '#/main/app/content/list/constants'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

const EvaluationActivities = (props) =>
  <ToolPage
    title={trans('activities')}
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

    <Button
      className="btn btn-primary w-100 mb-3"
      type={MODAL_BUTTON}
      primary={true}
      size="lg"
      label={trans('add_resources')}
      modal={[MODAL_RESOURCES, {
        root: props.workspaceRoot,
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          callback: () => props.addRequiredResources(props.contextId, selected)
        })
      }]}
    />
  </ToolPage>

EvaluationActivities.propTypes = {
  contextId: T.string.isRequired,
  addRequiredResources: T.func.isRequired,
  workspaceRoot: T.object.isRequired
}

export {
  EvaluationActivities
}
