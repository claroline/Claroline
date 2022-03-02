import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {scrollTo} from '#/main/app/dom/scroll'
import {matchPath} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'

import {Tab as TabTypes} from '#/plugin/home/prop-types'

const PlayerMenu = (props) => {
  function getTabSummary(tab) {
    return {
      type: LINK_BUTTON,
      icon: tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined,
      label: tab.title,
      target: `${props.path}/${tab.slug}`,
      active: !!matchPath(props.location.pathname, {path: `${props.path}/${tab.slug}`}),
      activeStyle: {
        borderColor: get(tab, 'display.color')
      },
      displayed: !tab.restrictions || !tab.restrictions.hidden,
      children: tab.children ? tab.children.map(getTabSummary) : [],
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }
  }

  if (0 < props.tabs.length) {
    return (
      <ContentSummary
        links={props.tabs.map(getTabSummary)}
      />
    )
  }

  return null
}

PlayerMenu.propTypes = {
  path: T.string,
  location: T.shape({
    pathname: T.string
  }).isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  autoClose: T.func.isRequired
}

PlayerMenu.defaultProps = {
  tabs: []
}

export {
  PlayerMenu
}
