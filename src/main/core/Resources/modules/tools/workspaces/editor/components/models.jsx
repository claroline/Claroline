import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'

import {WorkspaceList} from '#/main/core/workspace/components/list'
import {selectors} from '#/main/core/tools/workspaces/editor/store/selectors'
import {MODAL_WORKSPACE_CREATION} from '#/main/core/workspace/modals/creation'

const ModelExplain = () =>
  <div className="d-flex bg-body-tertiary rounded-3 mb-4 p-3">
    <div className="flex-fill">
      <b className="d-block text-uppercase mb-3">Les modèles copient :</b>

      <ul className="list-unstyled mb-0 d-grid gap-2">
        <li>
          <span className="fa fa-fw fa-check-circle text-success me-2" aria-hidden={true} />
          Contenus et ressources
        </li>
        <li>
          <span className="fa fa-fw fa-check-circle text-success me-2" aria-hidden={true} />
          Rôles et permissions
        </li>
        <li>
          <span className="fa fa-fw fa-check-circle text-success me-2" aria-hidden={true} />
          Paramètres de l'espace et des outils
        </li>
      </ul>
    </div>

    <div className="flex-fill">
      <b className="d-block text-uppercase mb-3">Les modèles ne copient pas :</b>

      <ul className="list-unstyled mb-0 d-grid gap-2">
        <li>
          <span className="fa fa-fw fa-times-circle text-danger me-2" aria-hidden={true} />
          Membres (utilisateurs et groupes)
        </li>
        <li>
          <span className="fa fa-fw fa-times-circle text-danger me-2" aria-hidden={true} />
          Activité des membres
        </li>
      </ul>
    </div>
  </div>

const EditorModels = () =>
  <EditorPage
    title={trans('models')}
    help={trans('workspace_models_desc', {}, 'workspace')}
  >
    <ModelExplain />

    <Button
      className="btn btn-primary mb-3 align-self-start"
      type={MODAL_BUTTON}
      label={trans('add_workspace_model')}
      modal={[MODAL_WORKSPACE_CREATION]}
    />

    <WorkspaceList
      url={['apiv2_workspace_list_model']}
      name={selectors.MODELS_LIST_NAME}
    />
  </EditorPage>

export {
  EditorModels
}
