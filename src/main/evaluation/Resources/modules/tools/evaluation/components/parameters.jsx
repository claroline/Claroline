import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ToolPage} from '#/main/core/tool/containers/page'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

import resourcesSource from '#/main/core/data/sources/resources'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

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
      }
    ]}
  >
    <AlertBlock
      style={{marginTop: 20}}
      type="info"
      title={trans('workspace_requirements_help_title', {}, 'evaluation')}
    >
      {trans('workspace_requirements_help_description', {}, 'evaluation')}
    </AlertBlock>

    <ListData
      name={selectors.STORE_NAME+'.requiredResources'}
      fetch={{
        url: ['apiv2_workspace_required_resource_list', {workspace: props.contextId}],
        autoload: true
      }}
      delete={{
        url: ['apiv2_workspace_required_resource_remove', {workspace: props.contextId}]
      }}
      primaryAction={resourcesSource.parameters.primaryAction}
      definition={resourcesSource.parameters.definition}
      card={resourcesSource.parameters.card}
      display={{
        current: listConst.DISPLAY_TILES_SM
      }}
    />

    <Button
      className="btn btn-emphasis btn-block"
      type={MODAL_BUTTON}
      primary={true}
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
