import React from 'react'
import {PropTypes as T} from 'prop-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'

const EditorMenu = props =>
  <ContentSummary
    links={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('parameters'),
        target: `${props.path}/edit/parameters`,
        onClick: (e) => {
          props.autoClose(e)
          scrollTo('.main-page-content')
        }
      }
    ]}
  />

EditorMenu.propTypes = {
  path: T.string.isRequired,
  autoClose: T.func.isRequired
}

export {
  EditorMenu
}
