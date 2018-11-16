import React from 'react'
import classes from 'classnames'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {getPlainText} from '#/main/app/data/html/utils'
import {number} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Button} from '#/main/app/action/components/button'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element'
import {Heading} from '#/main/core/layout/components/heading'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {DataCard as DataCardTypes} from '#/main/app/content/card/prop-types'

const CardAction = props => {
  if (!props.action || props.action.disabled) {
    // no action defined
    return (
      <div className={props.className}>
        {props.children}
      </div>
    )
  }

  return (
    <Button
      {...omit(props.action, 'group', 'icon', 'label', 'context', 'scope')}
      label={props.children}
      className={props.className}
    />
  )
}

CardAction.propTypes = {
  className: T.string,
  action: T.shape(merge({}, ActionTypes.propTypes, {
    label: T.node // make label optional
  })),
  children: T.any.isRequired
}

/**
 * Renders the card header.
 *
 * @param props
 * @constructor
 */
const CardHeader = props => {
  let headerStyles
  if (props.poster) {
    headerStyles = {
      backgroundImage: 'url(' + props.poster + ')',
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    }
  } else if (props.color) {
    headerStyles = {
      background: props.color
    }
  }

  return (
    <div className="data-card-header" style={headerStyles}>
      {props.icon &&
        <CardAction action={props.action} className="data-card-icon">
          {typeof props.icon === 'string' ?
            <span className={props.icon} /> :
            props.icon
          }
        </CardAction>
      }

      {0 !== props.flags.length &&
        <div className="data-card-flags">
          {props.flags.map((flag, flagIndex) => flag &&
            <TooltipElement
              key={flagIndex}
              id={`data-card-${props.id}-flag-${flagIndex}`}
              tip={flag[1]}
            >
              {undefined !== flag[2] ?
                <span className="data-card-flag">
                  {number(flag[2], true)}
                  <span className={flag[0]} />
                </span> :
                <span className={classes('data-card-flag', flag[0])} />
              }
            </TooltipElement>
          )}
        </div>
      }
    </div>
  )
}

CardHeader.propTypes = {
  id: T.oneOfType([
    T.string,
    T.number
  ]).isRequired,
  icon: T.oneOfType([T.string, T.element]),
  poster: T.string,
  color: T.string,
  flags: T.arrayOf(
    T.arrayOf(T.oneOfType([T.string, T.number]))
  ),
  action: T.shape(merge({}, ActionTypes.propTypes, {
    label: T.node // make label optional
  }))
}

/**
 * Renders a card representation of a data object.
 *
 * @param props
 * @constructor
 */
const DataCard = props =>
  <div className={classes(`data-card data-card-${props.orientation} data-card-${props.size}`, props.className, {
    'data-card-clickable': props.primaryAction && !props.primaryAction.disabled,
    'data-card-poster': !!props.poster || !!props.color
  })}>
    <CardHeader
      id={props.id}
      icon={-1 !== props.display.indexOf('icon') ? props.icon : undefined}
      color={props.color}
      poster={props.poster}
      flags={-1 !== props.display.indexOf('flags') ? props.flags : []}
      action={props.primaryAction}
    />

    <CardAction
      action={props.primaryAction}
      className="data-card-content"
    >
      <Heading
        key="data-card-title"
        level={props.level}
        className="data-card-title"
      >
        {props.title}
        {-1 !== props.display.indexOf('subtitle') && props.subtitle &&
          <small>{props.subtitle}</small>
        }
      </Heading>

      {'sm' !== props.size && -1 !== props.display.indexOf('description') && props.contentText &&
        <div key="data-card-description" className="data-card-description">
          {getPlainText(props.contentText)}
        </div>
      }

      {props.children}

      {'sm' !== props.size && -1 !== props.display.indexOf('footer') && props.footer &&
        <div key="data-card-footer" className="data-card-footer">
          {props.footer}
        </div>
      }
    </CardAction>

    {0 !== props.actions.length &&
      <Toolbar
        id={`actions-${props.id}`}
        className="data-card-toolbar"
        buttonName="btn btn-link"
        tooltip="left"
        toolbar="more"
        actions={props.actions}
        scope="object"
      />
    }
  </div>

implementPropTypes(DataCard, DataCardTypes)

export {
  DataCard
}
