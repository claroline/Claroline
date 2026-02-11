import {TrainingWorkspaceRestrictions} from '#/plugin/cursus/workspace/containers/restrictions'

/**
 * Adds a page to register to the parent training if the workspace is linked to one.
 */
export default (workspace, error) => ({
  component: TrainingWorkspaceRestrictions,
  displayed: 'NOT_REGISTERED_TRAINING' === error.code // only display the restriction if there is training info
})
