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
      props.tabs[0] && {from: '/', exact: true, to: '/' + props.tabs[0].slug }
    ].filter(redirect => !!redirect)}
    routes={[
      {
        path: '/:slug',
        onEnter: (params = {}) => props.setCurrentTab(params.slug),
        render: (routeProps) => {
          if (props.tabs.find(tab => tab.slug === routeProps.match.params.slug)) {
            const Player = (
              <PlayerTab
                path={props.path}
                currentContext={props.currentContext}
                tabs={props.tabs}
                currentTabTitle={props.currentTabTitle}
                currentTab={props.currentTab}
                editable={props.editable}
                administration={props.administration}
                desktopAdmin={props.desktopAdmin}
                widgets={props.widgets}
                setAdministration={props.setAdministration}
                fetchTabs={props.desktopAdmin ? props.fetchTabs : () => false}
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
  administration: T.bool.isRequired,
  desktopAdmin: T.bool.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired,

  setCurrentTab: T.func.isRequired,
  setAdministration: T.func.isRequired,
  fetchTabs: T.func.isRequired
}

export {
  PlayerMain
}
