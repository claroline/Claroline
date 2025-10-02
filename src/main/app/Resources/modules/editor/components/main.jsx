import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useHistory} from 'react-router-dom'
import Modal from 'react-bootstrap/Modal'
import {Helmet} from 'react-helmet'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'

import {EditorMenu} from '#/main/app/editor/components/menu'
import {EditorContext} from '#/main/app/editor/context'

import {theme} from '#/main/app/config/theme'

const Editor = (props) => {
  const pages = [
    {
      name: 'overview',
      title: trans('overview'),
      disabled: !props.overviewPage,
      component: props.overviewPage,
      standard: true
    }, {
      name: 'appearance',
      title: trans('appearance'),
      disabled: !props.appearancePage,
      component: props.appearancePage,
      standard: true
    }, {
      name: 'permissions',
      title: trans('permissions'),
      disabled: !props.permissionsPage,
      component: props.permissionsPage,
      standard: true,
      managerOnly: true
    }, {
      name: 'history',
      title: trans('history'),
      disabled: !props.historyPage,
      component: props.historyPage,
      advanced: true
    }, {
      name: 'actions',
      title: trans('advanced_actions', {}, 'actions'),
      disabled: !props.actionsPage,
      component: props.actionsPage,
      advanced: true
    }
  ]
    .concat(props.pages || [])
    .filter(page => !page.disabled && (!page.managerOnly || props.canAdministrate))

  const history = useHistory()

  return (
    <EditorContext.Provider
      value={{
        name: props.name,
        title: props.title,
        target: props.target,
        onSave: props.onSave,
        close: props.close,
        canAdministrate: !!props.canAdministrate
      }}
    >
      {(!isEmpty(props.styles) || props.title) &&
        <Helmet>
          <title>{props.title} - {trans('edition')}</title>

          {!isEmpty(props.styles) && props.styles.map(style =>
            <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
          )}
        </Helmet>
      }

      <Modal
        show={true}
        fullscreen={true}
        className="app-editor"
        animation={false}
        backdrop={false}
        enforceFocus={false}
        onHide={() => {
          // TODO : check pending changes
          history.push(props.close)
        }}
        aria-label={`${props.title} - ${trans('edition')}`}
      >
        <EditorMenu
          path={props.path}
          title={props.title}
          pages={pages}
          thumbnail={props.thumbnail}
        />

        <div className="app-editor-body" role="presentation">
          <Routes
            path={props.path}
            redirect={!isEmpty(pages) ? [
              {from: '/', exact: true, to: '/' + pages[0].name}
            ] : undefined}
            routes={pages.map(page => ({
              path: page.path || '/' + page.name,
              ...omit(page)
            }))}
          />
        </div>
      </Modal>
    </EditorContext.Provider>
  )
}

Editor.propTypes = {
  path: T.string.isRequired,

  /**
   * The name of the editor store we will connect to.
   */
  name: T.string.isRequired,
  /**
   * The API endpoint called to submit the form data.
   */
  target: T.oneOfType([
    // a plain URL
    T.string,
    // a symfony route
    T.array,
    // a func to generate the target of the form
    // it receives the form data and the isNew flag has params.
    T.func
  ]).isRequired,
  onSave: T.func,
  close: T.oneOfType([
    // a plain URL
    T.string,
    // a symfony route
    T.array
  ]),
  canAdministrate: T.bool,
  styles: T.array,
  title: T.string.isRequired,
  thumbnail: T.node,
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    title: T.string.isRequired,
    help: T.string,
    disabled: T.bool,
    managerOnly: T.bool,
    actions: T.arrayOf(T.shape({

    })),
    component: T.elementType,
    render: T.func
  })),

  // standard pages
  overviewPage: T.elementType,
  appearancePage: T.elementType,
  historyPage: T.elementType,
  permissionsPage: T.elementType,
  actionsPage: T.elementType
}

export {
  Editor
}
