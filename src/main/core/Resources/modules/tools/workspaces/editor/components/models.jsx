import React, {useState} from 'react'
import {useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'
import {actions as listActions} from '#/main/app/content/list'

import {WorkspaceList} from '#/main/core/workspace/components/list'
import {selectors} from '#/main/core/tools/workspaces/editor/store/selectors'
import {MODAL_WORKSPACE_CREATION} from '#/main/core/workspace/modals/creation'
import {Collapse} from 'react-bootstrap'

const ModelExplain = ({show = false}) =>
  <Collapse in={show}>
    <div className="flex-row bg-body-tertiary rounded-3 p-3 gap-0">
      <div className="flex-fill">
        <b className="d-block text-uppercase mb-3">{trans('models_copy', {}, 'workspace')}</b>

        <ul className="list-unstyled mb-0 d-grid gap-2">
          <li>
            <span className="fa fa-fw fa-check-circle text-success me-2" aria-hidden={true} />
            {trans('models_copy_content', {}, 'workspace')}
          </li>
          <li>
            <span className="fa fa-fw fa-check-circle text-success me-2" aria-hidden={true} />
            {trans('models_copy_roles', {}, 'workspace')}
          </li>
          <li>
            <span className="fa fa-fw fa-check-circle text-success me-2" aria-hidden={true} />
            {trans('models_copy_parameters', {}, 'workspace')}
          </li>
        </ul>
      </div>

      <div className="flex-fill">
        <b className="d-block text-uppercase mb-3">{trans('models_dont_copy', {}, 'workspace')}</b>

        <ul className="list-unstyled mb-0 d-grid gap-2">
          <li>
            <span className="fa fa-fw fa-times-circle text-danger me-2" aria-hidden={true} />
            {trans('models_dont_copy_members', {}, 'workspace')}
          </li>
          <li>
            <span className="fa fa-fw fa-times-circle text-danger me-2" aria-hidden={true} />
            {trans('models_dont_copy_activity', {}, 'workspace')}
          </li>
        </ul>
      </div>
    </div>
  </Collapse>

const EditorModels = () => {
  const dispatch = useDispatch()
  const [showHelp, setShowHelp] = useState(false)

  return (
    <EditorPage
      title={trans('models')}
      help={trans('workspace_models_desc', {}, 'workspace')}
    >
      <div className="data-form-section form-primary-section">
        <div className="d-flex flex-row gap-1" role="presentation">
          <Button
            className="btn btn-primary align-self-start"
            type={MODAL_BUTTON}
            label={trans('add_workspace_model', {}, 'actions')}
            modal={[MODAL_WORKSPACE_CREATION, {
              model: true,
              onCreate: () => dispatch(listActions.invalidateData(selectors.MODELS_LIST_NAME))
            }]}
          />
          <Button
            className="btn btn-text-body focus-ring"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-question-circle"
            label={trans(showHelp ? 'hide_help' : 'show_help', {}, 'actions')}
            callback={() => setShowHelp(!showHelp)}
          />
        </div>

        <ModelExplain show={showHelp} />

        <WorkspaceList
          url={['apiv2_workspace_list_model']}
          name={selectors.MODELS_LIST_NAME}
        />
      </div>
    </EditorPage>
  )
}

export {
  EditorModels
}
