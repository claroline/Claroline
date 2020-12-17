import merge from 'lodash/merge'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {getResource} from '#/main/core/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {selectors} from '#/main/core/resource/modals/creation/store/selectors'

// action creators
export const actions = {}

/**
 * Starts the creation of the selected resource type.
 * It initializes the new resource node with the default & parent values.
 *
 * @param {object} parent       - the parent of the new resource
 * @param {object} resourceType - the type of resource to create
 */
actions.startCreation = (parent, resourceType) => (dispatch, getState) => {
  let defaultData = {
    resource: null,
    resourceNode: merge({}, ResourceNodeTypes.defaultProps, {
      id: makeId(),
      autoId: 0, // this is just to avoid prop-types fail. It's not used and will be removed
      workspace: parent.workspace,
      meta: {
        mimeType: `custom/${resourceType.name}`,
        type: resourceType.name,
        creator: securitySelectors.currentUser(getState()),
        published: true
      },
      restrictions: parent.restrictions,
      rights: parent.rights
    })
  }

  // let the plugin add some changes to init data if it wants to
  return getResource(resourceType.name).then(module => {
    if (module.default && module.default.create) {
      // plugin wants to customize init data
      defaultData = module.default.create(defaultData)
    }

    // fill form reducer with new data
    dispatch(formActions.resetForm(selectors.STORE_NAME, defaultData, true))
  })
}

actions.reset = () => formActions.resetForm(selectors.STORE_NAME, {resource: {}, resourceNode: {}}, true)

/**
 * Shortcut to update the new node.
 *
 * @param {string} prop  - the name of the node's prop to update
 * @param {*}      value - the new value for the node's prop
 */
actions.updateNode = (prop, value) => formActions.updateProp(selectors.STORE_NAME, `${selectors.FORM_NODE_PART}.${prop}`, value)

/**
 * Shortcut to update the new resource.
 *
 * @param {string} prop  - the name of the resource's prop to update
 * @param {*}      value - the new value for the resource's prop
 */
actions.updateResource = (prop, value) => formActions.updateProp(selectors.STORE_NAME, prop ? `${selectors.FORM_RESOURCE_PART}.${prop}`:selectors.FORM_RESOURCE_PART, value)

/**
 * Saves the new resource.
 *
 * @param {object} parent - the parent of the new resource
 */
actions.create = (parent) => formActions.saveForm(selectors.STORE_NAME, ['claro_resource_action', {
  type: parent.meta.type,
  action: 'add',
  id: parent.id
}])
