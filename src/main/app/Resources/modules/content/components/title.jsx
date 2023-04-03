import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {toKey} from '#/main/core/scaffolding/text'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'

const HeadingWrapper = props  =>
  React.createElement(`h${props.level}`, Object.assign({},
    omit(props, 'level', 'displayLevel', 'displayed', 'align'),
    {
      className: classes(
        'h-title',
        props.className,
        props.displayLevel && `h${props.displayLevel}`,
        !props.displayed && 'sr-only',
        props.align && `text-${props.align}`
      )
    }
  ), props.children)

HeadingWrapper.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  displayed: T.bool,
  align: T.oneOf(['left', 'center', 'right']),
  children: T.any.isRequired
}

HeadingWrapper.defaultProps = {
  displayed: true,
  align: 'left'
}

const ContentTitle = props =>
  <HeadingWrapper
    {...omit(props, 'numbering', 'title', 'subtitle', 'backAction', 'actions')}
  >
    {!isEmpty(props.backAction) &&
      <Button
        className="btn h-back"
        label={trans('back')}
        {...props.backAction}
        icon="fa fa-fw fa-arrow-left"
        tooltip="bottom"
      />
    }

    {props.numbering &&
      <span className="h-numbering">{props.numbering}</span>
    }

    {props.children}

    <span role="presentation" className={classes(
      props.align && `text-${props.align}`
    )}>
      {props.title}

      {props.subtitle &&
        <small>{props.subtitle}</small>
      }
    </span>

    {!isEmpty(props.actions) &&
      <Toolbar
        id={props.id || toKey(props.title)}
        className="h-toolbar"
        buttonName="btn"
        tooltip="bottom"
        toolbar="more"
        size="sm"
        actions={props.actions}
      />
    }
  </HeadingWrapper>

ContentTitle.propTypes = {
  id: T.string,
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  numbering: T.node,
  title: T.node.isRequired,
  subtitle: T.string,
  displayed: T.bool,
  align: T.oneOf(['left', 'center', 'right']),
  backAction: T.shape({
    // TODO : action types
  }),
  actions: T.arrayOf(T.shape({
    // TODO : action types
  })),
  children: T.node
}

ContentTitle.defaultProps = {
  level: 2,
  align: 'left',
  displayed: true
}

export {
  ContentTitle
}
