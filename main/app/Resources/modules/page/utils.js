import {trans} from '#/main/core/translation'

const GROUP_SEPARATOR  = '|'
const ACTION_SEPARATOR = ' '

/**
 *
 * @param {string} toolbarConfig
 */
function parseToolbar(toolbarConfig) {
  if (toolbarConfig) {
    return toolbarConfig
      .split(GROUP_SEPARATOR)
      .reduce((actions, group) => actions.concat(group.split(ACTION_SEPARATOR)), [])
      .map(action => action.trim())
  }

  return []
}

/**
 *
 * @param {string} toolbarConfig
 * @param {Array}  actions
 */
function buildToolbar(toolbarConfig, actions) {
  let toolbar = []

  // retrieves defined actions groups
  const config = parseToolbar(toolbarConfig)

  // we want to know if there is action that are not configured in the toolbar
  let rest = actions.slice()

  if (0 !== config.length) {
    toolbar = config.map(
      // loop over each group
      group => group.map(
        // loop over each defined action to retrieve them
        actionName => {
          const pos = actions.findIndex(action => actionName === action.id)
          if (-1 !== pos) {
            rest.splice(pos, 1)

            return actions[pos]
          }
        }
      ).filter(action => !!action)
    )
  }

  if (0 < rest.length) {
    // append remaining actions to the configured toolbar
    toolbar.push([
      {
        id: 'more', // todo : it will break in lists
        type: 'menu',
        icon: 'fa fa-ellipsis-v',
        label: trans('show-more-actions', {}, 'actions'),
        menu: {
          menuLabel: trans('actions'),
          align: 'right', // I hope it wil not cause problems to not be able to configure it
          items: rest
        }
      }
    ])
  }

  return toolbar
}

export {
  buildToolbar
}
