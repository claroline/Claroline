import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {PlayerTab} from '#/plugin/home/tools/home/player/components/tab'

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
  setCurrentTab: T.func.isRequired
}

export {
  PlayerMain
}
