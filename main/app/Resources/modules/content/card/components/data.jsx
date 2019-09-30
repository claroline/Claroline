import React from 'react'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {getPlainText} from '#/main/app/data/types/html/utils'
import {number} from '#/main/app/intl'
import {Await} from '#/main/app/components/await'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Button} from '#/main/app/action/components/button'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {Heading} from '#/main/core/layout/components/heading'

import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes
} from '#/main/app/action/prop-types'
import {DataCard as DataCardTypes} from '#/main/app/content/card/prop-types'

// TODO : maybe manage it in action module (it's duplicated for tables)
const StaticCardAction = props => {
  if (isEmpty(props.action) || props.action.disabled || (props.action.displayed !== undefined && !props.action.displayed)) {
    return (
      <span className={props.className}>
        {props.children}
      </span>
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

StaticCardAction.propTypes = {
  className: T.string,
  action: T.shape(merge({}, ActionTypes.propTypes, {
    label: T.node // make label optional
  })),
  children: T.node.isRequired
}

const CardAction = props => {
  if (props.action instanceof Promise) {
    return (
      <Await
        for={props.action}
        then={action => (
          <StaticCardAction
            className={props.className}
            action={action}
          >
            {props.children}
          </StaticCardAction>
        )}
        placeholder={
          <span className={props.className}>
            {props.children}
          </span>
        }
      />
    )
  }

  return (
    <StaticCardAction
      className={props.className}
      action={props.action}
    >
      {props.children}
    </StaticCardAction>
  )
}

CardAction.propTypes = {
  className: T.string,
  action: T.oneOfType([
    // a regular action
    T.shape(merge({}, ActionTypes.propTypes, {
      label: T.node // make label optional
    })),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  children: T.any.isRequired
}

/**
 * Renders the card header.
 *
 * @param props
 * @constructor
 */
const CardHeader = props => {
  let headerStyles = {}
  if (props.poster) {
    headerStyles.backgroundImage = `url(${props.poster})`
    headerStyles.backgroundSize = 'cover'
    headerStyles.backgroundPosition = 'center'
  }

  if (props.color) {
    headerStyles.backgroundColor = props.color
  }

  return (
    <div className="data-card-header" style={!isEmpty(headerStyles) ? headerStyles : undefined}>
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
            <TooltipOverlay
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
            </TooltipOverlay>
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
  action: T.oneOfType([
    // a regular action
    T.shape(merge({}, ActionTypes.propTypes, {
      label: T.node // make label optional
    })),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ])
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

      {-1 === ['xs', 'sm'].indexOf(props.size) && -1 !== props.display.indexOf('description') && props.contentText &&
        <div key="data-card-description" className="data-card-description">
          {getPlainText(props.contentText)}
        </div>
      }

      {props.children}

      {-1 === ['xs', 'sm'].indexOf(props.size) && -1 !== props.display.indexOf('footer') && props.footer &&
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
