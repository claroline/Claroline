import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {getPlainText} from '#/main/core/data/types/html/utils'
import {ActionDropdownButton} from '#/main/core/layout/action/components/dropdown'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element'

import {DataCard as DataCardTypes} from '#/main/core/data/prop-types'

/**
 * Renders the card header.
 *
 * @param props
 * @constructor
 */
const CardHeader = props =>
  <div className="data-card-header" style={props.poster && {
    backgroundImage: 'url(' + props.poster + ')',
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }}>
    {props.icon &&
      <span className="data-card-icon">
        {typeof props.icon === 'string' ?
          <span className={props.icon} /> :
          props.icon
        }
      </span>
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
                <span className={flag[0]} />
                {flag[2]}
              </span> :
              <span className={classes('data-card-flag', flag[0])} />
            }
          </TooltipElement>
        )}
      </div>
    }
  </div>

CardHeader.propTypes = {
  id: T.string.isRequired,
  icon: T.oneOfType([T.string, T.element]).isRequired,
  poster: T.string,
  flags: T.arrayOf(
    T.arrayOf(T.oneOfType([T.string, T.number]))
  )
}

/**
 * Renders the card content.
 *
 * @param props
 * @constructor
 */
const CardContent = props => {
  if (!props.action || props.disabled) {
    return (
      <div className="data-card-content">
        {props.children}
      </div>
    )
  } else {
    if (typeof props.action === 'string') {
      return (
        <a role="link" href={props.action} className="data-card-content">
          {props.children}
        </a>
      )
    } else {
      return (
        <a role="button" onClick={props.action} className="data-card-content">
          {props.children}
        </a>
      )
    }
  }
}

CardContent.propTypes = {
  disabled: T.bool,
  action: T.oneOfType([T.string, T.func]),
  children: T.any.isRequired
}

CardContent.defaultProps = {
  disabled: false
}

/**
 * Renders a card representation of a data object.
 *
 * @param props
 * @constructor
 */
const DataCard = props =>
  <div className={classes(`data-card data-card-${props.orientation} data-card-${props.size}`, props.className)}>
    <CardHeader
      id={props.id}
      icon={props.icon}
      poster={props.poster}
      flags={props.flags}
    />

    <CardContent
      disabled={props.primaryAction && props.primaryAction.disabled}
      action={props.primaryAction && props.primaryAction.action}
    >
      {React.createElement(`h${props.level}`, {
        key: 'data-card-title',
        className: 'data-card-title'
      }, [
        props.title,
        props.subtitle &&
        <small key="data-card-subtitle">{props.subtitle}</small>
      ])}

      {'sm' !== props.size && props.contentText &&
        <div key="data-card-description" className="data-card-description">
          {getPlainText(props.contentText)}
        </div>
      }

      {'sm' !== props.size && props.footer &&
        <div key="data-card-footer" className="data-card-footer">
          {props.footer}
        </div>
      }
    </CardContent>

    {0 !== props.actions.length &&
      <ActionDropdownButton
        id={`${props.id}-btn`}
        className="data-actions-btn btn-link-default"
        bsStyle="link"
        noCaret={true}
        pullRight={true}
        actions={props.actions}
      />
    }
  </div>

implementPropTypes(DataCard, DataCardTypes)

export {
  DataCard
}
