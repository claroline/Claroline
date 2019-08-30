import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

function getWalkthrough(tools = [], authenticated = false, display = {}) {
  const walkthrough = [
    // Intro
    {
      highlight: ['.app-header-container'],
      content: {
        title: trans('header.intro.title', {}, 'walkthrough'),
        message: trans('header.intro.message', {}, 'walkthrough')
      },
      position: {
        target: '.app-header-container',
        placement: 'bottom'
      }
    }
  ]

  // Tools
  if (0 !== tools.length) {
    walkthrough.push({
      highlight: ['#app-tools'],
      content: {
        title: trans('desktop_tools', {}, 'walkthrough'),
        message: trans('header.tools_group.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-tools',
        placement: 'bottom'
      },
      requiredInteraction: {
        type: 'click',
        target: '#app-tools',
        message: trans('header.tools_group.action', {}, 'walkthrough')
      }
    })

    // help for each tool
    tools.map(tool => walkthrough.push({
      highlight: [`#app-tools-${tool.name}`],
      content: {
        icon: `fa fa-${tool.icon}`,
        title: trans('tool', {toolName: trans(tool.name, {}, 'tools')}, 'walkthrough'),
        message: trans(`header.tools.${tool.name}.message`, {}, 'walkthrough'),
        link: trans(`header.tools.${tool.name}.documentation`, {}, 'walkthrough')
      },
      position: {
        target: `#app-tools-${tool.name}`,
        placement: 'right'
      }
    }))
  }

  // Workspaces
  walkthrough.push({
    highlight: ['#app-workspaces-menu'],
    content: {
      title: trans('header.workspaces_menu.title', {}, 'walkthrough'),
      message: trans('header.workspaces_menu.message', {}, 'walkthrough')
    },
    position: {
      target: '#app-workspaces-menu',
      placement: 'bottom'
    }/*,
     requiredInteraction: {
     type: 'click',
     target: '#app-workspaces-menu',
     message: trans('header.workspaces_menu.action', {}, 'walkthrough')
     }*/

  })

  if (authenticated) {
    // Notifications
    walkthrough.push({
      highlight: ['#app-notifications-menu'],
      content: {
        title: trans('header.notifications.title', {}, 'walkthrough'),
        message: trans('header.notifications.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-administration',
        placement: 'bottom'
      }/*,
       requiredInteraction: {
       type: 'click',
       target: '#app-notifications-menu',
       message: trans('header.app-notifications-menu.action', {}, 'walkthrough')
       }*/
    })

    // User menu
    walkthrough.push({
      highlight: ['#app-user'],
      content: {
        title: trans('header.user_menu.title', {}, 'walkthrough'),
        message: trans('header.user_menu.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-administration',
        placement: 'bottom'
      }/*,
       requiredInteraction: {
       type: 'click',
       target: '#app-user',
       message: trans('header.user_menu.action', {}, 'walkthrough')
       }*/
    })
  } else {
    // TODO : anonymous user menu doc
  }

  // Locale menu
  if (get(display, 'locale')) {
    walkthrough.push({
      highlight: ['#app-locale-select'],
      content: {
        message: trans('header.locale.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-locale-select',
        placement: 'bottom'
      }
    })
  }

  return walkthrough
}

export {
  getWalkthrough
}
