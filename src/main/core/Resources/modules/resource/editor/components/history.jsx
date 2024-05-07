import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const ResourceEditorHistory = () =>
  <EditorPage
    title={trans('history')}
    help={trans('Retrouvez toutes les modifications effectuées sur votre ressource et son contenu.')}
  >
    <div className="p-4">
      <p>Cette page contiendra les logs opérationnels liés à la ressource</p>
      <p>Afficher aussi ici le créateur, date de création, date de dernière modifications</p>
      <ul>
        <li>Logs liés à ResourceNode (la configuration standard de la ressource)</li>
        <li>Logs liés aux entités de la ressource ?</li>
      </ul>
    </div>
  </EditorPage>

export {
  ResourceEditorHistory
}
