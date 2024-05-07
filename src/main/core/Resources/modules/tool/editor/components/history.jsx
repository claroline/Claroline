import React from 'react'
import {EditorPage} from '#/main/app/editor'
import {trans} from '#/main/app/intl'

const ToolEditorHistory = () =>
  <EditorPage
    title={trans('history')}
    help={trans('Retrouvez toutes les modifications effectuées sur vos contenus.')}
  >
    <div className="p-4">
      <p>Cette page contiendra les logs opérationnels liés à l'outil</p>
      <p>Afficher aussi ici le créateur, date de création, date de dernière modifications</p>
      <ul>
        <li>Logs liés à OrderedTool (la configuration standard de l'outil)</li>
        <li>Logs liés aux entités de l'outil ? (ex. logs des Users, Groups dans l'outil Communauté)</li>
      </ul>
    </div>
  </EditorPage>

export {
  ToolEditorHistory
}
