import {RestrictionMain} from '#/plugin/cursus/workspace/restriction/components/main'

/**
 * Adds a page to register to the parent training if the workspace is linked to one.
 */
export default (workspace, errors) => ({
  component: RestrictionMain,
  displayed: !!errors.trainings, // only display the restriction if there is training info
  order: 0
})
