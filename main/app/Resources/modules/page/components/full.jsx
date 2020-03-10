import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {implementPropTypes} from '#/main/app/prop-types'

import {PageFull as PageFullTypes} from '#/main/app/page/prop-types'
import {PageSimple} from '#/main/app/page/components/simple'
import {PageHeader} from '#/main/app/page/components/header'
import {PageContent} from '#/main/app/page/components/content'

/**
 * Root of the current page.
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
        disabled={props.disabled}
        actions={props.actions}
      />
    }

    <PageContent className={classes({'main-page-content': !props.embedded})}>
      {props.children}
    </PageContent>
  </PageSimple>

implementPropTypes(PageFull, PageFullTypes)

export {
  PageFull
}
