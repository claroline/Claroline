import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'
import {actions as formActions} from '#/main/app/content/form/store'
import {select as workspaceSelectors} from '#/main/core/workspace/selectors'

import {TeamTool as TeamToolComponent} from '#/plugin/team/tools/team/components/tool'
import {selectors, actions} from '#/plugin/team/tools/team/store'

const TeamTool = withRouter(
  connect(
    (state) => ({
      canEdit: selectors.canEdit(state),
      teamParams: selectors.teamParams(state),
      resourceTypes: selectors.resourceTypes(state),
      workspaceId: workspaceSelectors.workspace(state).uuid
    }),
    (dispatch) => ({
      resetForm(formData) {
        dispatch(formActions.resetForm('teamParamsForm', formData))
      },
      openCurrentTeam(id, teamParams, workspaceId, resourceTypes) {
        dispatch(actions.openForm('teams.current', id, {
          id: makeId(),
          workspace: {
            uuid: workspaceId
          },
          selfRegistration: teamParams.selfRegistration,
          selfUnregistration: teamParams.selfUnregistration,
          publicDirectory: teamParams.publicDirectory,
          deletableDirectory: teamParams.deletableDirectory,
          creatableResources: resourceTypes
        }))
      },
      resetCurrentTeam() {
        dispatch(formActions.resetForm('teams.current', {}, true))
      },
      openMultipleTeamsForm(teamParams, resourceTypes) {
        dispatch(formActions.resetForm('teams.multiple', {
          nbTeams: 1,
          selfRegistration: teamParams.selfRegistration,
          selfUnregistration: teamParams.selfUnregistration,
          publicDirectory: teamParams.publicDirectory,
          deletableDirectory: teamParams.deletableDirectory,
          creatableResources: resourceTypes
        }, true))
      },
      resetMultipleTeamsForm() {
        dispatch(formActions.resetForm('teams.multiple', {}, true))
      }
    })
  )(TeamToolComponent)
)

export {
  TeamTool
}