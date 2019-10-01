import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'
import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TeamTool as TeamToolComponent} from '#/plugin/team/tools/team/components/tool'
import {selectors, actions} from '#/plugin/team/tools/team/store'

const TeamTool = withRouter(
  connect(
    (state) => ({
      canEdit: selectors.canEdit(state),
      teamParams: selectors.teamParams(state),
      resourceTypes: selectors.resourceTypes(state),
      workspaceId: toolSelectors.contextData(state) ? toolSelectors.contextData(state).uuid : null
    }),
    (dispatch) => ({
      resetForm(formData) {
        dispatch(formActions.resetForm(selectors.STORE_NAME + '.teamParamsForm', formData))
      },
      openCurrentTeam(id, teamParams, workspaceId, resourceTypes) {
        dispatch(actions.openForm(selectors.STORE_NAME + '.teams.current', id, {
          id: makeId(),
          workspace: {
            uuid: workspaceId
          },
          selfRegistration: teamParams.selfRegistration,
          selfUnregistration: teamParams.selfUnregistration,
          publicDirectory: teamParams.publicDirectory,
          deletableDirectory: teamParams.deletableDirectory,
          creatableResources: resourceTypes.map(type => type.name)
        }))
      },
      resetCurrentTeam() {
        dispatch(formActions.resetForm(selectors.STORE_NAME + '.teams.current', {}, true))
      },
      openMultipleTeamsForm(teamParams, resourceTypes) {
        dispatch(formActions.resetForm(selectors.STORE_NAME + '.teams.multiple', {
          nbTeams: 1,
          selfRegistration: teamParams.selfRegistration,
          selfUnregistration: teamParams.selfUnregistration,
          publicDirectory: teamParams.publicDirectory,
          deletableDirectory: teamParams.deletableDirectory,
          creatableResources: resourceTypes.map(type => type.name)
        }, true))
      },
      resetMultipleTeamsForm() {
        dispatch(formActions.resetForm(selectors.STORE_NAME + '.teams.multiple', {}, true))
      }
    })
  )(TeamToolComponent)
)

export {
  TeamTool
}