import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {APIClass} from '#/plugin/scorm/resources/scorm/player/api'
import {Player as PlayerComponent} from '#/plugin/scorm/resources/scorm/player/components/player'
import {selectors} from '#/plugin/scorm/resources/scorm/store'
import {flattenScos} from '#/plugin/scorm/resources/scorm/utils'

const Player = connect(
  state => ({
    currentUser: securitySelectors.currentUser(state),
    workspaceUuid: resourceSelectors.resourceNode(state).workspace.id,
    path: resourceSelectors.path(state),
    scorm: selectors.scorm(state),
    trackings: selectors.trackings(state),
    scos: flattenScos(selectors.scos(state))
  }),
  dispatch => ({
    initializeScormAPI(sco, scorm, tracking, currentUser) {
      const scormApi = new APIClass(sco, scorm, tracking[sco.id] || {}, dispatch, currentUser)

      window.API = scormApi
      window.api = scormApi
      window.API_1484_11 = scormApi
      window.api_1484_11 = scormApi
    }
  })
)(PlayerComponent)

export {
  Player
}
