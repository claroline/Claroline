import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {scrollTo} from '#/main/app/dom/scroll'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'

const PlayerMenu = props => {

  let baseLinks = []
  if (props.overview) {
    baseLinks = [{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-home',
      label: trans('home'),
      target: props.path,
      exact: true,
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }]
  }

  let endLink = []
  if (props.showEndPage) {
    endLink = [{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-flag-checkered',
      label: trans('end'),
      target: props.path + '/play/end',
      exact: true,
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }]
  }

  return (
    <ContentSummary
      links={baseLinks.concat(endLink)}
    />
  )
}

PlayerMenu.propTypes = {
  path: T.string.isRequired,
  overview: T.bool,
  showEndPage: T.bool,
  autoClose: T.func
}

export {
  PlayerMenu
}
