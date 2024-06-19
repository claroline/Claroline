import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Alert} from '#/main/app/components/alert'

import {MODAL_RESOURCES} from '#/main/core/modals/resources'
import {ResourceList} from '#/main/core/resource/components/list'

import {selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationParameters = (props) =>
  <ToolPage
    subtitle={trans('parameters')}
    actions={[
      {
        name: 'initialize',
        type: ASYNC_BUTTON,
        icon: 'fa fa-fw fa-sync',
        label: trans('initialize_evaluations', {}, 'evaluation'),
        request: {
          url: ['apiv2_workspace_evaluations_init', {workspace: props.contextId}],
          request: {
            method: 'PUT'
          }
        }
      }, {
        name: 'recompute',
        type: ASYNC_BUTTON,
        icon: 'fa fa-fw fa-calculator',
        label: trans('recompute_evaluations', {}, 'evaluation'),
        request: {
          url: ['apiv2_workspace_evaluations_recompute', {workspace: props.contextId}],
          request: {
            method: 'PUT'
          }
        }
      }, {
        name: 'download_all_workspace_certificates',
        type: ASYNC_BUTTON,
        icon: 'fa fa-fw fa-file-zipper',
        label: trans('download_all_workspace_certificates', {}, 'actions'),
        request: {
          url: ['apiv2_workspace_download_all_certificates', {workspace: props.contextId}],
          request: {
            method: 'GET'
          }
        }
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

EvaluationParameters.propTypes = {
  contextId: T.string.isRequired,
  addRequiredResources: T.func.isRequired,
  workspaceRoot: T.object.isRequired
}

export {
  EvaluationParameters
}
