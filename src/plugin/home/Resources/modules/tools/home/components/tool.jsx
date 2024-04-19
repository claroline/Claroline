import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Tool} from '#/main/core/tool'

import {PlayerMain} from '#/plugin/home/tools/home/player/containers/main'
import {HomeEditor} from '#/plugin/home/tools/home/editor/containers/main'
import {getTabSummary} from '#/plugin/home/tools/home/utils'

const HomeTool = props => {
  const tabs = props.tabs
    .filter(tab => props.showHidden || !get(tab, 'restrictions.hidden', false))
    .map((tab) => getTabSummary(props.path, tab, false))

  return (
    <Tool
      {...props}
      styles={['claroline-distribution-plugin-home-home-tool']}
      menu={1 < tabs.length ? tabs : []}
      editor={HomeEditor}
    >
      <PlayerMain/>
    </Tool>
  )
}

HomeTool.propTypes = {
  path: T.string.isRequired,
  tabs: T.array
}

HomeTool.defaultProps = {
  tabs: []
}

export {
  HomeTool
}
