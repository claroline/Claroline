import React from 'react'

const EditorHistory = () =>
  <div className="p-4">
    <p>Cette page contiendra les logs opérationnels liés à la ressource</p>
    <p>Afficher aussi ici le créateur, date de création, date de dernière modifications</p>
    <ul>
      <li>Logs liés à ResourceNode (la configuration standard de la ressource)</li>
      <li>Logs liés aux entités de la ressource ?</li>
    </ul>
  </div>

EditorHistory.propTypes = {

}

export {
  EditorHistory
}
