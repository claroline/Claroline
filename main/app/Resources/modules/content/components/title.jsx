import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'

const HeadingWrapper = props  =>
  React.createElement(`h${props.level}`, Object.assign({},
    omit(props, 'level', 'displayLevel', 'displayed', 'align'),
    {
      className: classes(
        'h-title',
        props.className,
        props.displayLevel && `h${props.displayLevel}`,
        !props.displayed && 'sr-only',
        `text-${props.align}`
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
    {...omit(props, 'numbering', 'title', 'subtitle')}
  >
    {!isEmpty(props.backAction) &&
      <Button
        label={trans('back')}
        {...props.backAction}
        icon="fa fa-fw fa-arrow-left"
        tooltip="bottom"
      />
    }

    {props.numbering &&
      <span className="h-numbering">{props.numbering}</span>
    }

    {props.title}

    {props.subtitle &&
      <small>{props.subtitle}</small>
    }

    {!isEmpty(props.actions) &&
      <Button
        className="h-toolbar"
        type={MENU_BUTTON}
        icon="fa fa-fw fa-ellipsis-v"
        label={trans('show-more-actions', {}, 'actions')}
        tooltip="bottom"
        menu={{
          align: 'right',
          items: props.actions
        }}
      />
    }
  </HeadingWrapper>

ContentTitle.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  numbering: T.string,
  title: T.string.isRequired,
  subtitle: T.string,
  displayed: T.bool,
  align: T.oneOf(['left', 'center', 'right']),
  children: T.any,
  backAction: T.shape({
    // TODO : action types
  }),
  actions: T.arrayOf(T.shape({
    // TODO : action types
  }))
}

export {
  ContentTitle
}
