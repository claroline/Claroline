import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useHistory} from 'react-router-dom'
import Modal from 'react-bootstrap/Modal'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'

import {EditorMenu} from '#/main/app/editor/components/menu'
import {AppLoader} from '#/main/app/layout/components/loader'
import {EditorContext} from '#/main/app/editor/context'
import isEmpty from 'lodash/isEmpty'
import {Helmet} from 'react-helmet'
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
      help: trans('Personnalisez les paramètres d\'affichage avancés de vos contenus.'),
      disabled: !props.appearancePage,
      component: props.appearancePage,
      standard: true
    }, {
      name: 'permissions',
      title: trans('permissions'),
      help: trans('Gérez les différents droits d\'accès et de modifications de vos utilisateurs.'),
      disabled: !props.permissionsPage,
      component: props.permissionsPage,
      standard: true,
      managerOnly: true
    }, {
      name: 'history',
      title: trans('history'),
      help: trans('Retrouvez toutes les modifications effectuées sur vos contenus.'),
      disabled: !props.historyPage,
      component: props.historyPage,
      advanced: true
    }, {
      name: 'actions',
      title: trans('Actions avancées'),
      help: trans('Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?'),
      disabled: !props.actionsPage,
      component: props.actionsPage,
      advanced: true
    }
  ]
    .concat(props.pages)
    .filter(page => !page.disabled && (!page.managerOnly || props.canAdministrate))

  const history = useHistory()

  return (
    <EditorContext.Provider
      value={{
        name: props.name,
        target: props.target,
        onSave: props.onSave,
        close: props.close,
        canAdministrate: props.canAdministrate
      }}
    >
      {(!isEmpty(props.styles) || props.title) &&
        <Helmet>
          {props.title &&
            <title>{props.title} - {trans('edition')}</title>
          }

          {props.styles.map(style =>
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
        onHide={() => {
          // TODO : check pending changes
          history.push(props.close)
        }}
      >
        <AppLoader />

        <EditorMenu
          path={props.path}
          title={props.title}
          pages={pages}
        />

        <div className="app-editor-body" role="presentation">
          <Routes
            path={props.path}
            redirect={props.defaultPage ? [
              {from: '/', exact: true, to: '/' + props.defaultPage}
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
  title: T.string.isRequired,
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
  defaultPage: T.string,

  // standard pages
  overviewPage: T.elementType,
  appearancePage: T.elementType,
  historyPage: T.elementType,
  permissionsPage: T.elementType,
  actionsPage: T.elementType
}

Editor.defaultProps = {
  pages: [],
  actions: [],
  styles: [],
  canAdministrate: false
}

export {
  Editor
}
