import React from 'react'
import omit from 'lodash/omit'

import {implementPropTypes} from '#/main/app/prop-types'

import {PageFull as PageFullTypes} from '#/main/app/page/prop-types'
import {PageSimple} from '#/main/app/page/components/simple'
import {PageHeader} from '#/main/app/page/components/header'
import {PageContent} from '#/main/app/page/components/content'

/**
 * Root of the current page.
 *
 * For now, modals are managed here.
 * In future version, when the layout will be in React,
 * it'll be moved in higher level.
 */
const PageFull = props =>
  <PageSimple
    {...omit(props, 'showHeader', 'title', 'subtitle', 'icon', 'poster', 'toolbar', 'actions')}
  >
    {props.showHeader &&
      <PageHeader
        title={props.title}
        subtitle={props.subtitle}
        icon={props.icon}
        poster={props.poster}
        toolbar={props.toolbar}
        actions={props.actions}
      />
    }

    <PageContent>
      {props.children}
    </PageContent>
  </PageSimple>

implementPropTypes(PageFull, PageFullTypes)

export {
  PageFull
}
