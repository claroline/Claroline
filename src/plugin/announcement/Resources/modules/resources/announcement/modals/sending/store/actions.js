import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as baseActions} from '#/plugin/announcement/resources/announcement/store/actions'

import {selectors} from '#/plugin/announcement/resources/announcement/modals/sending/store/selectors'

export const actions = {}

actions.sendAnnounce = (aggregateId, announce) => (dispatch) => {
  dispatch(formActions.saveForm(
    selectors.STORE_NAME+'.form',
    ['claro_announcement_update', {aggregateId: aggregateId, id: announce.id}]
  )).then((response) => dispatch(baseActions.changeAnnounce(response)))
}
