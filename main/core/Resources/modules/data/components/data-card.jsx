import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {getPlainText} from '#/main/core/data/types/html/utils'
import {number} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {GenericButton} from '#/main/app/button/components/generic'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element'
import {Heading} from '#/main/core/layout/components/heading'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {DataCard as DataCardTypes} from '#/main/core/data/prop-types'

/**
 * Renders the card header.
 *
 * @param props
 * @constructor
 */
const CardHeader = props =>
  <div className={classes('data-card-header', props.poster && 'data-card-header-poster')} style={props.poster && {
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
                {number(flag[2], true)}
                <span className={flag[0]} />
              </span> :
              <span className={classes('data-card-flag label label-default', flag[0])} />
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
  if (!props.action || props.action.disabled) {
    // no action defined
    return (
      <div className="data-card-content">
        {props.children}
      </div>
    )
  } else {
    return (
      <GenericButton
        {...omit(props.action, 'group', 'icon', 'label', 'context', 'scope')}
        className="data-card-content"
      >
        {props.children}
      </GenericButton>
    )
  }
}

CardContent.propTypes = {
  action: T.shape(
    ActionTypes.propTypes
  ),
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
  <div className={classes(`data-card data-card-${props.orientation} data-card-${props.size}`, props.className, {
    'data-card-clickable': props.primaryAction && !props.primaryAction.disabled,
    'data-card-poster': !!props.poster
  })}>
    <CardHeader
      id={props.id}
      icon={props.icon}
      poster={props.poster}
      flags={props.flags}
    />

    <CardContent
      action={props.primaryAction}
    >
      <Heading
        key="data-card-title"
        level={props.level}
        className="data-card-title"
      >
        {props.title}
        {props.subtitle &&
          <small>{props.subtitle}</small>
        }
      </Heading>

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
  DataCard,
  CardHeader as DataCardHeader,
  CardContent as DataCardContent
}
