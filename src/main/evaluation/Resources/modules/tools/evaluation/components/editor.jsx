import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {Alert} from '#/main/app/components/alert'

import {MODAL_RESOURCES} from '#/main/core/modals/resources'
import {ResourceList} from '#/main/core/resource/components/list'

import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {constants as toolConstants} from '#/main/core/tool/constants'
import {EditorParameters} from '#/main/community/tools/community/editor/containers/parameters'
import {EditorProfile} from '#/main/community/tools/community/editor/containers/profile'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'

const EvaluationEditor = (props) =>
  <ToolEditor
    /*menu={[
      {
        name: 'overview',
        label: trans('about'),
        type: LINK_BUTTON,
        target: props.path+'/edit',
        exact: true
      }, {
        name: 'profile',
        type: LINK_BUTTON,
        label: trans('user_profile'),
        target: `${props.path}/edit/profile`,
        displayed: props.contextType === toolConstants.TOOL_DESKTOP
      }
    ]}*/
  >
    {props.contextId &&
      <>
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
      </>
    }
  </ToolEditor>

EvaluationEditor.propTypes = {
  contextId: T.string.isRequired,
  addRequiredResources: T.func.isRequired,
  workspaceRoot: T.object.isRequired
}

export {
  EvaluationEditor
}
