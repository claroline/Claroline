import React from 'react'
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

const StaticCardAction = props => {
  if (isEmpty(props.action) || props.action.disabled || (props.action.displayed !== undefined && !props.action.displayed)) {
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
 * Renders a card representation of a data object.
 *
 * @param props
 * @constructor
 */
const DataCard = props => {
  const asIcon = props.asIcon || 'row' === props.orientation

  return (
    <article style={props.style} className={classes(`data-card data-card-${props.orientation} data-card-${props.size}`, props.className, {
      'data-card-clickable': props.primaryAction && !props.primaryAction.disabled,
      'data-card-poster': !props.asIcon && (!!props.poster || !!props.color || !!props.icon),
    })}>
      <Thumbnail
        thumbnail={props.poster}
        color={props.color}
        size={props.size}
        square={asIcon}
        className={classes({
          'my-2': 'xs' === props.size
        })}
      >
        {typeof props.icon === 'string' ?
          <span className={props.icon} /> :
          props.icon
        }
      </Thumbnail>

      <CardAction
        action={props.primaryAction}
        className={classes('data-card-content text-reset text-decoration-none', {
          'text-center': 'row' !== props.orientation && asIcon,
          'py-2': 'xs' === props.size
        })}
      >
        <Heading
          level={props.level}
          className={classes('data-card-title', {
            'mb-0': 'xs' === props.size
          })}
        >
          {props.title}
        </Heading>

        {-1 !== props.display.indexOf('description') &&
          <p className={classes('data-card-description text-body-secondary', {
            'mb-0': -1 !== ['xs', 'sm'].indexOf(props.size) || !props.meta || (-1 === props.display.indexOf('meta') && -1 === props.display.indexOf('flags'))
          })}>
            {props.contentText && getPlainText(props.contentText)}
          </p>
        }

        {props.children}

        {-1 === ['xs', 'sm'].indexOf(props.size) && props.meta && (-1 !== props.display.indexOf('meta') || -1 !== props.display.indexOf('flags')) &&
          <div className={classes('d-flex flex-row flex-wrap align-items-center gap-1 mt-auto', {
            'justify-content-center': 'row' !== props.orientation && asIcon
          })} role="presentation">
            {props.meta}
          </div>
        }
      </CardAction>

      {0 !== props.actions.length &&
        <Toolbar
          id={`actions-${props.id}`}
          name="data-card-toolbar"
          buttonName="btn btn-text-body"
          tooltip="left"
          toolbar={props.toolbar}
          actions={props.actions}
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
