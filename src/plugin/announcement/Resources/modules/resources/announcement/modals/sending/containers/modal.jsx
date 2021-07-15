import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store/actions'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

import {SendingModal as SendingModalComponent} from '#/plugin/announcement/resources/announcement/modals/sending/components/modal'
import {actions, reducer, selectors} from '#/plugin/announcement/resources/announcement/modals/sending/store'

const SendingModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME+'.form')),
      workspace: resourceSelectors.workspace(state)
    }),
    (dispatch) => ({
      send(aggregateId, announce) {
        dispatch(actions.sendAnnounce(aggregateId, announce))
      },
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME+'.form', prop, value))
      },
      reset(announcement, workspaceRoles) {
        let data = announcement
        if (!announcement.roles || 0 === announcement.roles.length) {
          // by default select all ws roles for sending
          data = merge({}, announcement, {
            roles: workspaceRoles
          })
        }

        dispatch(listActions.addFilter(selectors.STORE_NAME+'.receivers', 'roles', data.roles.map(role => role.id)))
        dispatch(formActions.resetForm(selectors.STORE_NAME+'.form', data))
        dispatch(listActions.invalidateData(selectors.STORE_NAME+'.receivers'))
      },
      updateReceivers(roleIds) {
        dispatch(listActions.addFilter(selectors.STORE_NAME+'.receivers', 'roles', roleIds))
        dispatch(listActions.invalidateData(selectors.STORE_NAME+'.receivers'))
      }
    })
  )(SendingModalComponent)
)

export {
  SendingModal
}
