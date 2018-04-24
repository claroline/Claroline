import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'

import {constants} from '#/main/core/widget/constants'
import {App as ListApp} from '#/main/core/widget/types/list'
import {ResourceCardMinimal} from '#/main/core/resource/data/components/resource-card'

import {actions as listWidgetActions} from '#/main/core/widget/types/list/actions'

/**
 * Resource list widget application (implements list widget).
 *
 * @param context
 * @param parameters
 * @constructor
 */
const App = (context, parameters) => ListApp(context, merge({}, parameters, {
  fetchUrl: constants.CONTEXT_DESKTOP === context.type ?
    ['apiv2_widget_resource_list_desktop'] :
    ['apiv2_widget_resource_list_ws', {workspace: context.data.id}],

  // todo clean and make generic
  primaryAction: (resourceNode, dispatch) => 'directory' !== resourceNode.meta.type ? ({
    type: 'url',
    label: trans('open', {}, 'actions'),
    target: ['claro_resource_open', {node: resourceNode.id, resourceType: resourceNode.meta.type}]
  }) : ({
    type: 'callback',
    label: trans('open', {}, 'actions'),
    callback: () => {
      // changes the target of the list to add current directory in URL
      const fetchUrl = constants.CONTEXT_DESKTOP === context.type ?
        ['apiv2_widget_resource_list_desktop', {parent: resourceNode.id}] :
        ['apiv2_widget_resource_list_ws', {workspace: context.data.id, parent: resourceNode.id}]

      dispatch(listWidgetActions.updateWidgetConfig({
        title: resourceNode.name,
        fetchUrl: fetchUrl,
        display: 'list', // todo mega hack
        availableDisplays: ['list']  // todo double mega hack
      }))
    }
  }),

  definition: [
    {
      name: 'name',
      label: trans('name'),
      displayed: true,
      primary: true
    }, {
      name: 'meta.created',
      label: trans('creation_date'),
      type: 'date',
      alias: 'creationDate',
      displayed: true,
      filterable: false
    }
  ],
  card: ResourceCardMinimal
}))

export {
  App
}
