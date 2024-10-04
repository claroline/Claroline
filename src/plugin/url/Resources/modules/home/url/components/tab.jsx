import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {HomePage} from '#/plugin/home/tools/home/components/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

import {UrlDisplay} from '#/plugin/url/components/display'

const UrlTab = props =>
  <HomePage
    currentTab={props.currentTab}
    title={props.title}
  >
    <UrlDisplay
      url={get(props.currentTab, 'parameters.url')}
      mode={get(props.currentTab, 'parameters.mode')}
      ratio={get(props.currentTab, 'parameters.ratio')}
    />
  </HomePage>

UrlTab.propTypes = {
  currentContext: T.object,
  title: T.string.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  )
}

export {
  UrlTab
}
