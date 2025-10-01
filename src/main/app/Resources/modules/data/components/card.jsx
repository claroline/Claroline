import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {getPlainText} from '#/main/app/data/types/html/utils'
import {Await} from '#/main/app/components/await'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Button} from '#/main/app/action/components/button'
import {Heading} from '#/main/app/components/heading'

import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes
} from '#/main/app/action/prop-types'
import {DataCard as DataCardTypes} from '#/main/app/data/prop-types'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {Badge} from '#/main/app/components/badge'

const StaticCardAction = props => {
  if (props.disabled || isEmpty(props.action) || props.action.disabled || (props.action.displayed !== undefined && !props.action.displayed)) {
    return (
      <span className={props.className} role="presentation">
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
  disabled: T.bool,
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
            disabled={props.disabled}
          >
            {props.children}
          </StaticCardAction>
        )}
        placeholder={
          <span className={props.className} role="presentation">
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
      disabled={props.disabled}
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
  disabled: T.bool,
  children: T.any.isRequired
}

/**
 * Renders a card representation of a data object.
 *
 * @param props
 * @constructor
 */
const DataCard = props => {
  const id = useId()
  const asIcon = props.asIcon || 'row' === props.orientation
  const disabled = !props.loaded || props.invalidated

  return (
    <article style={props.style} className={classes(`data-card data-card-${props.orientation} data-card-${props.size}`, props.className, {
      'data-card-clickable': !disabled && props.primaryAction && !props.primaryAction.disabled,
      'data-card-poster': !asIcon && (!!props.poster || !!props.color || !!props.name || !!props.icon),
      'placeholder-glow': !props.loaded,
      'data-card-loading': !props.loaded || props.invalidated,
      'data-card-invalidated placeholder-glow': props.loaded && props.invalidated
    })} title={props.title}>
      {'col' === props.orientation && props.status &&
        <Badge className="data-card-status" variant={props.status.variant}>
          {props.status.text}
        </Badge>
      }

      <Thumbnail
        loaded={props.loaded}
        name={props.name}
        thumbnail={props.poster}
        color={props.color}
        size={props.size}
        square={asIcon}
        className={classes('data-card-thumbnail', {
          'rounded-circle': asIcon
        })}
      >
        {typeof props.icon === 'string' ?
          <span className={props.icon} aria-hidden={true} /> :
          props.icon
        }
      </Thumbnail>

      <CardAction
        action={props.primaryAction}
        className={classes('data-card-content text-reset text-decoration-none focus-ring z-1', {
          'text-center': 'row' !== props.orientation && asIcon
        })}
        disabled={disabled}
      >
        <Heading
          level={props.level}
          className={classes('data-card-title', {
            'mb-2': 'row' === props.orientation
          })}
        >
          {props.loaded ?
            props.title :
            <div className="placeholder rounded-1 w-75" role="presentation" />
          }
        </Heading>

        {!props.loaded && -1 !== props.display.indexOf('description') &&
          <p className={classes('data-card-description text-body-secondary', {
            'mb-0': -1 !== ['xs', 'sm'].indexOf(props.size) || !props.meta || (-1 === props.display.indexOf('meta') && -1 === props.display.indexOf('flags'))
          })}>
            <span className="placeholder rounded-1 w-100" role="presentation" />
            <span className="placeholder rounded-1 w-100" role="presentation" />
            <span className="placeholder rounded-1 w-50" role="presentation" />
          </p>
        }

        {props.loaded && -1 !== props.display.indexOf('description') && ('xs' !== props.size || !isEmpty(props.contentText)) &&
          <p className={classes('data-card-description text-body-secondary', {
            'mb-0': -1 !== ['xs', 'sm'].indexOf(props.size) || !props.meta || (-1 === props.display.indexOf('meta') && -1 === props.display.indexOf('flags'))
          })}>
            {props.contentText && typeof props.contentText === 'string' ?
              getPlainText(props.contentText) :
              props.contentText
            }
          </p>
        }

        {props.loaded && props.children}

        {-1 === ['xs', 'sm'].indexOf(props.size) && props.meta && (-1 !== props.display.indexOf('meta') || -1 !== props.display.indexOf('flags')) &&
          <div className={classes('d-flex flex-row flex-wrap align-items-center gap-1 mt-auto', {
            'justify-content-center': 'row' !== props.orientation && asIcon
          })} role="presentation">
            {props.loaded ?
              props.meta :
              <div className="badge text-body-tertiary placeholder w-25" role="presentation">&nbsp;</div>
            }
          </div>
        }
      </CardAction>

      {0 !== props.actions.length &&
        <Toolbar
          id={id}
          name="data-card-toolbar"
          buttonName="btn btn-text-body focus-ring focus-ring-secondary"
          tooltip={'row' === props.orientation ? 'left' : 'bottom'}
          toolbar={props.toolbar}
          actions={props.actions}
          disabled={disabled}
          scope="object"
        />
      }
    </article>
  )
}

DataCard.propTypes = DataCardTypes.propTypes
DataCard.defaultProps = DataCardTypes.defaultProps

export {
  DataCard
}
