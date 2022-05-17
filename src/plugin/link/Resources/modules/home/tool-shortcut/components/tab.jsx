import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {HomePage} from '#/plugin/home/tools/home/containers/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

import {route as desktopRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as adminRoute} from '#/main/core/administration/routing'
import {constants} from '#/main/core/tool/constants'

const ToolShortcutTab = props => {
  let redirect
  switch (props.currentContext.type) {
    case constants.TOOL_DESKTOP:
      redirect = desktopRoute(get(props.currentTab, 'parameters.tool'))
      break
    case constants.TOOL_ADMINISTRATION:
      redirect = adminRoute(get(props.currentTab, 'parameters.tool'))
      break
    case constants.TOOL_WORKSPACE:
      redirect = workspaceRoute(props.currentContext.data, get(props.currentTab, 'parameters.tool'))
      break
  }

  if (redirect) {
    window.location.href = url(['claro_index']) + '#' + redirect
  }

  // this is just to avoid a blank page in case of something goes wrong in the redirect
  return (
    <HomePage
      tabs={props.tabs}
      currentTab={props.currentTab}
      title={props.title}
    />
  )
}

ToolShortcutTab.propTypes = {
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }),
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  title: T.string.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  )
}

export {
  ToolShortcutTab
}
