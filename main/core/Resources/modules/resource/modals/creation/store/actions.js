import merge from 'lodash/merge'

import {makeActionCreator} from '#/main/app/store/actions'

import {makeId} from '#/main/core/scaffolding/id'
import {currentUser} from '#/main/core/user/current'
import {actions as formActions} from '#/main/core/data/form/actions'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {selectors} from '#/main/core/resource/modals/creation/store'

// action names
export const RESOURCE_SET_PARENT = 'RESOURCE_SET_PARENT'

// action creators
export const actions = {}

/**
 * Sets the parent of the new resource.
 *
 * @param {object} parent - the parent of the new resource
 */
actions.setParent = makeActionCreator(RESOURCE_SET_PARENT, 'parent')

/**
 * Starts the creation of the selected resource type.
 * It initializes the new resource node with the default & parent values.
 *
 * @param {object} parent       - the parent of the new resource
 * @param {object} resourceType - the type of resource to create
 */
actions.startCreation = (parent, resourceType) => (dispatch) => {
  dispatch(actions.setParent(parent))
  dispatch(formActions.resetForm(selectors.FORM_NAME, {
    resource: null,
    resourceNode: merge({}, ResourceNodeTypes.defaultProps, {
      id: makeId(),
      workspace: parent.workspace,
      meta: {
        mimeType: `custom/${resourceType.name}`,
        type: resourceType.name,
        creator: currentUser(),
        published: true
      },
      restrictions: parent.restrictions,
      rights: parent.rights
    })
  }, true))
}

/**
 * Shortcut to update the new node.
 *
 * @param {string} prop  - the name of the node's prop to update
 * @param {*}      value - the new value for the node's prop
 */
actions.updateNode = (prop, value) => formActions.updateProp(selectors.FORM_NAME, `${selectors.FORM_NODE_PART}.${prop}`, value)

/**
 * Shortcut to update the new resource.
 *
 * @param {string} prop  - the name of the resource's prop to update
 * @param {*}      value - the new value for the resource's prop
 */
actions.updateResource = (prop, value) => formActions.updateProp(selectors.FORM_NAME, `${selectors.FORM_RESOURCE_PART}.${prop}`, value)

/**
 * Saves the new resource.
 *
 * @param {object} parent - the parent of the new resource
 */
actions.create = (parent) => formActions.saveForm(selectors.FORM_NAME, ['claro_resource_action', {
  resourceType: parent.meta.type,
  action: 'add',
  id: parent.id
}])
