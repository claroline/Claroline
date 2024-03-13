import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

import {PageTitle} from '#/main/app/page/components/title'
import {PageNav} from '#/main/app/page/components/nav'

/**
 * Header of the current page.
 *
 * Contains title, icon, actions and an optional poster image.
 */
const PageHeader = props =>
  <header
    style={props.poster && {
      backgroundImage: `url("${asset(props.poster)}")`
    }}
    className={classes('page-header', {
      'page-poster': !!props.poster
    })}
  >
    <PageNav>
      {props.menu}
    </PageNav>

    <div className="page-header-content m-4 gap-4" role="presentation">
      {props.icon &&
        <div className="page-icon" role="presentation">
          {props.icon}
        </div>
      }

      <div className="d-flex flex-fill align-items-center" role="presentation">
        <PageTitle
          embedded={props.embedded}
          path={props.path}
          title={props.subtitle || props.title}
        />

        {(!isEmpty(props.primaryAction) || !isEmpty(props.actions) || props.actions instanceof Promise) &&
          <div className="page-actions gap-3 ms-auto">
            {props.primaryAction &&
              <Button
                {...props.primaryAction}
                className="btn btn-primary page-actions-btn"
                icon={undefined}
                tooltip={undefined}
              />
            }

            {(!isEmpty(props.actions) || props.actions instanceof Promise) &&
              <Toolbar
                id={props.id || toKey(props.title)}
                className="btn-toolbar gap-1"
                buttonName="btn page-actions-btn"
                tooltip="bottom"
                toolbar={props.toolbar}
                actions={props.actions}
                disabled={props.disabled}
                scope="object"
              />
            }
          </div>
        }
      </div>
    </div>
  </header>

PageHeader.propTypes = {
  id: T.string,
  showTitle: T.bool,
  showBreadcrumb: T.bool,
  title: T.string.isRequired,
  subtitle: T.node,
  icon: T.oneOfType([T.string, T.node]),
  embedded: T.bool,
  poster: T.string,
  disabled: T.bool,
  /**
   * The path of the page inside the application (used to build the breadcrumb).
   */
  path: T.arrayOf(T.shape({
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  })),

  toolbar: T.string,

  primaryAction: T.shape(
    ActionTypes.propTypes
  ),
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),

  /**
   * Custom children.
   *
   * Add your custom component here.
   */
  children: T.node
}

PageHeader.defaultProps = {
  actions: []
}

export {
  PageHeader
}
