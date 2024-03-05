import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {HomeTabs} from '#/plugin/home/tools/home/components/tabs'

const PlayerMenu = (props) =>
  <HomeTabs
    path={props.path}
    tabs={props.tabs}
  />

PlayerMenu.propTypes = {
  path: T.string,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  ))
}

export {
  PlayerMenu
}
