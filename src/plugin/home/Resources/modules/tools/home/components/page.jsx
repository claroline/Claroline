import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ToolPage} from '#/main/core/tool'

import {Tab as TabTypes} from '#/plugin/home/prop-types'

const HomePage = props =>
  <ToolPage
    className="home-tool"
    breadcrumb={props.breadcrumb}
    title={props.title}
    poster={props.poster || get(props.currentTab, 'poster')}
  >
    {props.children}
  </ToolPage>

HomePage.propTypes = {
  breadcrumb: T.array,
  title: T.string.isRequired,
  poster: T.string,
  currentTab: T.shape(
    TabTypes.propTypes
  ),
  children: T.any
}

HomePage.defaultProps = {
  path: '',
}

export {
  HomePage
}
