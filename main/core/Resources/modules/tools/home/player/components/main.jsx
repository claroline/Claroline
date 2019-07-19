import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {PlayerTab} from '#/main/core/tools/home/player/components/tab'

const PlayerMain = props =>
  <Routes
    path={props.path}
    redirect={[
      props.tabs[0] && {from: '/', exact: true, to: '/'+props.tabs[0].id }
    ].filter(redirect => !!redirect)}
    routes={[
      {
        path: '/:id',
        onEnter: (params = {}) => props.setCurrentTab(params.id),
        render: (routeProps) => {
          if (props.tabs.find(tab => tab.id === routeProps.match.params.id)) {
            const Player = (
              <PlayerTab
                path={props.path}
                currentContext={props.currentContext}
                tabs={props.tabs}
                currentTabTitle={props.currentTabTitle}
                currentTab={props.currentTab}
                editable={props.editable}
                widgets={props.widgets}
              />
            )

            return Player
          }

          // tab does not exist
          // let's redirection open the first available
          routeProps.history.replace(props.path)

          return null
        }
      }
    ]}
  />

PlayerMain.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string.isRequired,
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired,

  setCurrentTab: T.func.isRequired
}

export {
  PlayerMain
}
