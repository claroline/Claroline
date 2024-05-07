import React from 'react'
import classes from 'classnames'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {PageFull as PageFullTypes} from '#/main/app/page/prop-types'
import {PageSimple} from '#/main/app/page/components/simple'
import {PageHeader} from '#/main/app/page/components/header'

const PageFull = (props) =>
  <PageSimple
    {...omit(props, 'showHeader', 'title', 'icon', 'poster', 'toolbar', 'actions', 'menu')}
    className={classes(props.className, props.size && `page-${props.size}`)}
    meta={merge({}, {
      title: props.title,
      poster: props.poster
    }, props.meta || {})}
  >
    {props.showHeader &&
      <PageHeader
        id={props.id}
        breadcrumb={props.breadcrumb}
        title={props.title}
        icon={props.icon}
        poster={props.poster}
        toolbar={props.toolbar}
        disabled={props.disabled}
        primaryAction={props.primaryAction}
        secondaryAction={props.secondaryAction}
        actions={props.actions}
        menu={props.menu}
        embedded={props.embedded}
      />
    }

    <div role="presentation" className={classes('page-content container-fluid', {'main-page-content': !props.embedded})}>
      {props.children}
    </div>
  </PageSimple>

PageFull.propTypes = PageFullTypes.propTypes
PageFull.defaultProps = PageFullTypes.defaultProps

export {
  PageFull
}
