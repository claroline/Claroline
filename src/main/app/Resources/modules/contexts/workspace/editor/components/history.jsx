import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const WorkspaceEditorHistory = () =>
  <EditorPage
    title={trans('history')}
    help={trans('Retrouvez toutes les modifications effectuées sur vos contenus.')}
  >
    <div className="p-4">
      <p>Cette page contiendra les logs opérationnels liés à l'espace d'activité</p>
      <p>Afficher aussi ici le créateur, date de création, date de dernière modifications</p>
      <ul>
        <li>Logs liés au Workspace</li>
        <li>Logs liés aux entités des outils ?</li>
      </ul>
    </div>
  </EditorPage>

export {
    WorkspaceEditorHistory
}
